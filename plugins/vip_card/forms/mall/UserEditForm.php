<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/28
 * Time: 14:47
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\forms\common\CommonVipCard;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardOrder;
use app\plugins\vip_card\models\VipCardUser;

class UserEditForm extends Model
{
    public $id;
    public $detail_id;
    public $user_id;
    public $start_time;
    public $end_time;
    public $sign;
    private $sends;
    private $user;
    public $payType;
    public $isRecordOrder;

    public function rules()
    {
        return [
            [['id', 'detail_id', 'user_id'], 'integer'],
            [['sign'], 'string'],
            [['end_time',], 'required', 'on' => ['expire']]
        ];
    }

    public function attributeLabels()
    {
        return [

        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $user = VipCardUser::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$user) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $user = VipCardUser::findOne(['mall_id' => \Yii::$app->mall->id, 'user_id' => $this->user_id, 'is_delete' => 0]);
                $mallUser = User::findOne(['id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0 ]);
                if (!$mallUser) {
                    throw new \Exception('指定用户不存在');
                }
                if (!$user) {
                    $user = new VipCardUser();
                }
            }

            $detail = VipCardDetail::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->detail_id])
                ->with('vipCards')
                ->with('vipCoupons')
                ->with('main')
                ->asArray()
                ->one();
            if (!$detail) {
                throw new \Exception('会员卡不存在');
            }

            $userInfo = UserInfo::findOne(['user_id' => $this->user_id]);
            if (!$userInfo) {
                throw new \Exception('用户不存在');
            }

            $user->mall_id = \Yii::$app->mall->id;
            $user->main_id = $detail['main']['id'];
            $user->detail_id = $detail['id'];
            $user->user_id = $this->user_id;
            $user->image_name = $detail['name'];
            $user->image_main_name = $detail['main']['name'];
            $user->image_discount = $detail['main']['discount'];
            $user->image_is_free_delivery = $detail['main']['is_free_delivery'];
            $user->image_type = $detail['main']['type'];
            $user->image_type_info = $detail['main']['type_info'];

            $allSend['send_integral_num'] = $detail['send_integral_num'];
            $allSend['send_balance'] = $detail['send_balance'];
            $allSend['cards'] = $detail['cards'];
            $allSend['coupons'] = $detail['coupons'];
            $user->all_send = json_encode($allSend);
            $user->is_delete = 0;

            $this->sends = CommonVipCard::getCardDetail($this->detail_id);
            $this->user =  User::find()->where(['id' => $this->user_id])->with(['userInfo', 'identity'])->one();

            if ($this->isRecordOrder) {
                $token = \Yii::$app->security->generateRandomString();
                $order_id = CommonVip::getCommon()->generateOrders($this->detail_id, $token, [], true, $this->sign ?? '', $this->payType);
            }

            if ($user->isNewRecord) {
                $user->start_time = date('Y-m-d H:i:s', time());
                $user->end_time = date("Y-m-d", strtotime(" +{$detail['expire_day']} day"));
            } else {
                $date = $user->end_time;
                $user->end_time = date("Y-m-d", strtotime("{$date} +{$detail['expire_day']} day"));
            }
            $res = $user->save();

            $cardOrder = new VipCardOrder();
            $cardOrder->mall_id = \Yii::$app->mall->id;
            $cardOrder->order_id = $order_id ?? 0;
            $cardOrder->status = 1;
            $cardOrder->main_id = $detail['main']['id'];
            $cardOrder->main_name = $detail['main']['name'];
            $cardOrder->price = $detail['price'];
            $cardOrder->detail_id = $detail['id'];
            $cardOrder->user_id = $this->user_id;
            $cardOrder->detail_name = $detail['name'];
            $cardOrder->expire = $detail['expire_day'];
            $cardOrder->sign = $this->sign ?? '';
            $allSend['send_integral_num'] = $this->sends['send_integral_num'];
            $allSend['send_balance'] = $this->sends['send_balance'];
            $allSend['cards'] = $this->sends['cards'];
            $allSend['coupons'] = $this->sends['coupons'];
            $cardOrder->all_send = json_encode($allSend);
            $cardOrder->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            $this->doSend();
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'order_id' => $cardOrder->order_id,
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    public function switchExpire()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $user = VipCardUser::findOne(['mall_id' => \Yii::$app->mall->id, 'user_id' => $this->user_id]);
            if (!$user) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $user->end_time = $this->end_time;
            $user->save();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    private function doSend()
    {
        try {
            $this->sendCard();
            $this->sendCoupon();
            $this->giveIntegral();
            $this->giveBalance();
        } catch (\Exception $exception) {
            \Yii::error('========后台添加超级会员卡赠送失败========');
            \Yii::error($exception);
            throw new \Exception($exception->getMessage());
        }
    }

    private function sendCard()
    {
        CommonVipCard::sendCard($this->sends, \Yii::$app->mall->id, $this->user_id);
    }

    private function sendCoupon()
    {
        CommonVipCard::sendCoupon($this->sends, \Yii::$app->mall->id, $this->user_id);
    }

    private function giveIntegral()
    {
        $name = $this->sends['main']['name'] ?? '超级会员卡';
        if (isset($this->sends['send_integral_num']) && $this->sends['send_integral_num'] > 0) {
            \Yii::$app->currency->setUser($this->user)->integral
                ->add((int)$this->sends['send_integral_num'], $name . '赠送积分');
        }
        return true;
    }

    private function giveBalance()
    {
        $name = $this->sends['main']['name'] ?? '超级会员卡';
        if (isset($this->sends['send_balance']) && $this->sends['send_balance'] > 0) {
            \Yii::$app->currency->setUser($this->user)->balance
                ->add((float)$this->sends['send_balance'], $name . '赠送余额');
        }
        return true;
    }

}
