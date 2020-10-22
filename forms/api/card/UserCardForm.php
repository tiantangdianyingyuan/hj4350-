<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 14:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\card;


use app\core\response\ApiCode;
use app\forms\common\card\CommonUserCard;
use app\forms\common\CommonQrCode;
use app\models\ClerkUser;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\models\QrCodeParameter;
use app\models\UserCard;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class UserCardForm extends Model
{
    public $cardId;
    public $mall;
    public $user;

    public $is_clerk;
    public $clerk_id;
    public $keyword;
    public $use_number;
    public $qr_code_id;

    public function rules()
    {
        return [
            [['cardId', 'is_clerk', 'clerk_id', 'use_number', 'qr_code_id'], 'integer'],
            ['keyword', 'string']
        ];
    }

    public function getList()
    {
        try {
            $query = UserCard::find()
                ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

            if ($this->is_clerk && $this->is_clerk == 1) {
                $query->andWhere(['is_use' => 1]);
            }

            if ($this->is_clerk && $this->is_clerk == 2) {
                $query->andWhere(['is_use' => 0]);
            }


            if ($this->clerk_id) {
                $query->andWhere(['clerk_id' => $this->clerk_id]);
            } else {
                $query->andWhere(['>', 'end_time', date('Y-m-d H:i:s', time())]);
            }

            if ($this->keyword) {
                $userIds = User::find()
                    ->andWhere(['like', 'nickname', $this->keyword])
                    ->andWhere(['mall_id' => \Yii::$app->mall->id])
                    ->select('id');
                $query->andWhere(['or', ['user_id' => $userIds], ['like', 'name', $this->keyword]]);
            }

            $list = $query
                ->orderBy('id desc')
                ->with('user.userInfo', 'store')
                ->page($pagination)
                ->all();

            $newList = [];
            /** @var UserCard $item */
            foreach ($list as $item) {
                $newItem = [];
                $newItem['id'] = $item->id;
                $newItem['card_id'] = $item->card_id;
                $newItem['is_use'] = $item->is_use;
                $newItem['name'] = $item->name;
                $newItem['nickname'] = $item->user->nickname;
                $newItem['pic_url'] = $item->pic_url;
                $newItem['platform'] = $item->user->userInfo->platform;
                $newItem['store_id'] = $item->store_id;
                $newItem['store_name'] = $item->store ? $item->store->name : '';
                $newItem['user_id'] = $item->user_id;
                $newItem['number'] = $item->number;
                $newItem['use_number'] = $item->use_number;
                $newItem['receive_id'] = $item->receive_id;
                $newItem = array_merge($newItem, $item->getNewItem($item));
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'pagination' => $pagination,
                    'list' => $newList
                ]
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => $e
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonUserCard();
            $common->mall = $this->mall;
            $common->user = $this->user;
            $common->cardId = $this->cardId;
            $common->userId = $this->user->id;
            /** @var UserCard $card */
            $card = $common->detail();


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'card' => $this->getUserCard($card)
                ]
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => $e
            ];
        }
    }

    /**
     * @param UserCard $card
     * @return array
     * @throws \Exception
     */
    public function getUserCard($card)
    {
        $newCard = ArrayHelper::toArray($card);
        $newCard['card_name'] = $card->name;
        $newCard['endTime'] = strtotime($card->end_time);
        $newCard['cancel_status'] = $card->order ? $card->order->cancel_status : 0;
        $newCard['name'] = $card->order ? $card->order->name : '';
        $newCard['mobile'] = $card->order ? $card->order->mobile : '';
        $newCard['start_time'] = new_date($card->start_time);
        $newCard['end_time'] = new_date($card->end_time);
        $newCard['clerked_at'] = new_date($card->clerked_at);
        $newCard['created_at'] = new_date($card->created_at);
        $newCard['receive_id'] = $card->receive_id;
        $newCard['receive_user_name'] = '';
        if ($card->receive_id > 0) {
            $receive = User::findOne(['id' => $card->receive_id]);
            $newCard['receive_user_name'] = $receive->nickname;
            $newCard['status'] = 0; // 卡券已转赠
        } elseif ($card->is_use == 0 && $card->end_time > mysql_timestamp()) {
            $newCard['status'] = 1; // 卡券未使用
        } elseif ($card->is_use == 1) {
            $newCard['status'] = 2; // 卡券已使用
        } else {
            $newCard['status'] = 3; // 卡券已过期
        }
        $newItem = $card->getNewItem($card);
        $newCard = array_merge($newCard, $newItem);

        // 用户卡券详情页面有用到
        if (isset($this->qr_code_id) && $this->qr_code_id != -1 && $card->receive_id == 0) {
            $qrCodeParameter = QrCodeParameter::find()->andWhere(['id' => $this->qr_code_id, 'mall_id' => \Yii::$app->mall->id])->one();
            if (!$qrCodeParameter) {
                throw new \Exception("数据异常");
            }
            $newCard['clerk_number'] = $qrCodeParameter->use_number;
        }
        $newCard['app_share_title'] = $card->card->app_share_title;
        $newCard['app_share_pic'] = $card->card->app_share_pic;
        $newCard['is_allow_send'] = $card->card->is_allow_send;
        return $newCard;
    }

    public function clerk()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->qr_code_id != -1) {
                $qrCodeParameter = QrCodeParameter::find()->andWhere(['id' => $this->qr_code_id, 'mall_id' => \Yii::$app->mall->id, 'use_number' => 0])->one();
                if (!$qrCodeParameter) {
                    throw new \Exception("核销码已失效");
                }

                $qrCodeParameter->use_number = $qrCodeParameter->use_number + 1;
                $res = $qrCodeParameter->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($qrCodeParameter));
                }
            }

            if (!$this->use_number || $this->use_number < 1) {
                throw new \Exception('请输入核销次数');
            }

            $common = new CommonUserCard();
            $common->mall = $this->mall;
            $common->user = $this->user;
            $common->cardId = $this->cardId;
            $common->user = \Yii::$app->user->identity;
            $common->use_number = $this->use_number;
            $res = $common->clerk();

            //权限判断，用以核销后返回的页面判断
            $is_clerk = 1;
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (empty(\Yii::$app->plugin->getInstalledPlugin('clerk')) || !in_array('clerk', $permission) || empty(ClerkUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]))) {
                $is_clerk = 0;
            }
            $msg = $res['surplus_number'] > 0 ? '核销成功(剩余' . $res['surplus_number'] . '次)' : '核销成功';

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg,
                'data' => [
                    'is_clerk' => $is_clerk,
                    'surplus_number' => $res['surplus_number']
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'errors' => $e
            ];
        }
    }

    public function qrcode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonCard = new CommonUserCard();
            $commonCard->cardId = $this->cardId;
            $commonCard->mall = \Yii::$app->mall;
            $userCard = $commonCard->detail();
            if ($userCard->receive_id > 0) {
                throw new \Exception('卡券已转赠，无法生成核销码');
            }
            $common = new CommonQrCode();
            $img = $common->getQrCode(['cardId' => $this->cardId], 430, 'pages/card/clerk/clerk');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $img
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
