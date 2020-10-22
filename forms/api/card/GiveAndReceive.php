<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/28
 * Time: 17:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\card;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\Model;
use app\models\UserCard;

class GiveAndReceive extends Model
{
    public $cardId;

    public function rules()
    {
        return [
            ['cardId', 'integer']
        ];
    }

    public function give()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var UserCard $userCard */
            $userCard = $this->getUserCard();
            // 判断是否是本人转赠出去的卡券
            if ($userCard->user_id == \Yii::$app->user->id) {
                $form = new UserCardForm();
                $res = $form->getUserCard($userCard);
                $res['type'] = 'detail';
            } else {
                // 头像、昵称、状态使用转赠的用户卡券的信息
                $avatar = $userCard->user->userInfo->avatar;
                $nickname = $userCard->user->nickname;
                $status = $this->getStatus($userCard);
                // 判断是否是本人领取的卡券
                if ($userCard->receive_id == \Yii::$app->user->id) {
                    $userCard = UserCard::find()
                        ->where([
                            'parent_card_id' => $this->cardId,
                            'is_delete' => 0,
                            'mall_id' => \Yii::$app->mall->id
                        ])
                        ->with(['user.userInfo', 'detail'])
                        ->one();

                    if (!$userCard) {
                        throw new \Exception('卡券不存在，无效的信息');
                    }
                }
                $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app';
                $res = [
                    'avatar' => $avatar,
                    'nickname' => $nickname,
                    'name' => $userCard->name,
                    'pic_url' => $userCard->pic_url,
                    'reset_number' => $userCard->number - $userCard->use_number,
                    'is_use' => $userCard->is_use,
                    'start_time' => date('Y.m.d H:i:s', strtotime($userCard->start_time)),
                    'end_time' => date('Y.m.d H:i:s', strtotime($userCard->end_time)),
                    'status' => $status,
                    'id' => $userCard->id,
                    'receive_id' => $userCard->receive_id,
                    'card_bg' => $url . '/card/card_bg.png',
                    'img_card' => $url . '/card/img_card.png',
                    'img_finish_receiving' => $url . '/card/img_finish_receiving.png',
                    'type' => 'give',
                    'img_back' => $url . '/card/back.png',
                    'img_receive' => $url . '/card/receive.png',
                    'img_share' => $url . '/card/share-1.png',
                    'receive_card_bg' => $url . '/card/receive_card_bg.png',
                    'app_share_title' => $userCard->card->app_share_title,
                    'app_share_pic' => $userCard->card->app_share_pic,
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $res
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var UserCard $userCard */
            $userCard = $this->getUserCard();
            $status = $this->getStatus($userCard);
            /* @var UserCard $userCard */
            switch ($status) {
                case 0:
                    throw new \Exception('卡券已失效');
                    break;
                case 1:
                    if ($userCard->user_id == \Yii::$app->user->id) {
                        throw new \Exception('自己无法领取自己转赠的卡券');
                    }
                    $model = new UserCard();
                    $model->attributes = [
                        'mall_id' => $userCard->mall_id,
                        'user_id' => \Yii::$app->user->id,
                        'card_id' => $userCard->card_id,
                        'name' => $userCard->name,
                        'pic_url' => $userCard->pic_url,
                        'content' => $userCard->content,
                        'created_at' => mysql_timestamp(),
                        'is_use' => 0,
                        'clerk_id' => 0,
                        'store_id' => 0,
                        'clerked_at' => '0000-00-00 00:00:00',
                        'order_id' => $userCard->order_id,
                        'order_detail_id' => $userCard->order_detail_id,
                        'data' => $userCard->data,
                        'start_time' => mysql_timestamp(max(time(), strtotime($userCard->start_time))),
                        'end_time' => $userCard->end_time,
                        'number' => $userCard->number - $userCard->use_number,
                        'use_number' => 0,
                        'receive_id' => 0,
                        'parent_card_id' => $userCard->id,
                    ];
                    if (!$model->save()) {
                        throw new \Exception($this->getErrorMsg($model));
                    }
                    $userCard->receive_id = \Yii::$app->user->id;
                    if (!$userCard->save()) {
                        throw new \Exception($this->getErrorMsg($userCard));
                    }
                    break;
                case 2:
                    throw new \Exception('卡券已被领取');
                    break;
                case 3:
                    throw new \Exception('卡券已被领取');
                    break;
                case 4:
                    throw new \Exception('卡券所属订单发生退款行为，暂时不能领取');
                    break;
                default:
                    throw new \Exception('卡券已失效');
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '领取成功'
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array|\yii\db\ActiveRecord|null|UserCard
     * @throws \Exception
     */
    public function getUserCard()
    {
        $userCard = UserCard::find()
            ->where([
                'id' => $this->cardId,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])
            ->with(['user.userInfo', 'detail.refund', 'card', 'order'])
            ->one();

        if (!$userCard) {
            throw new \Exception('卡券不存在，无效的信息');
        }
        return $userCard;
    }

    /**
     * @param UserCard $userCard
     * @return int
     */
    public function getStatus($userCard)
    {
        if ($userCard->is_use == 1 || $userCard->number == $userCard->use_number || strtotime($userCard->end_time) <= time()) {
            $status = 0; // 卡券失效
        } elseif ($userCard->receive_id == 0) {
            $status = 1; // 卡券可领取
        } elseif ($userCard->receive_id == \Yii::$app->user->id) {
            $status = 2; // 卡券已领取
        } else {
            $status = 3; // 卡券已被他人领取
        }
        if ($userCard->order->cancel_status != 0 || ($userCard->detail->refund && $userCard->detail->refund != 3)) {
            $status = 4;
        }
        return $status;
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app';
            /* @var UserCard $userCard */
            $userCard = $this->getUserCard();
            $qrcodeFactory = new CommonQrCode();
            $qrcode = $qrcodeFactory->getQrCode(['user_id' => \Yii::$app->user->id, 'card_id' => $this->cardId], 430, 'pages/card/give/give');
            $res = [
                'avatar' => $userCard->user->userInfo->avatar,
                'nickname' => $userCard->user->nickname,
                'name' => $userCard->name,
                'pic_url' => $userCard->pic_url,
                'reset_number' => $userCard->number - $userCard->use_number,
                'start_time' => $userCard->start_time,
                'end_time' => $userCard->end_time,
                'qrcode' => $qrcode['file_path'],
                'poster_bg' => $url . '/card/poster_bg.png'
            ];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $res
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
