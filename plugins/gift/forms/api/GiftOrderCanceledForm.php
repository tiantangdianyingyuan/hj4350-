<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 */


namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\models\GoodsAttr;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;

class GiftOrderCanceledForm extends Model
{
    public $gift_id;

    /** @var GiftSendOrder $order */
    public $gift;

    public function rules()
    {
        return [
            [['gift_id'], 'required'],
            [['gift_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        \Yii::error('礼物订单未付款取消操作开始');
        $this->gift = GiftSendOrder::findOne(['gift_id' => $this->gift_id, 'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$this->gift) {
            \Yii::error('礼物不存在');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '礼物不存在'
            ];
        }
        if ($this->gift->is_cancel == 1) {
            \Yii::error('订单已被取消');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单已被取消'
            ];
        }
        if ($this->gift->is_pay == 1) {
            \Yii::error('订单已付款');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单已付款'
            ];
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->couponResume()->integralResume()->goodsAddStock()->orderCancel();
            $this->gift->is_cancel = 1;
            if (!$this->gift->save()) {
                throw new \Exception($this->gift->errors[0]);
            }
            if (GiftLog::updateAll(['is_cancel' => 1], ['id' => $this->gift_id]) <= 0) {
                throw new \Exception('礼物订单状态更新失败');
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            \Yii::error($e);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 用户积分恢复
     */
    protected function integralResume()
    {
        $user = User::findOne(['id' => $this->gift->user_id]);
        if ($this->gift->use_integral_num) {
            $desc = '商品订单取消，订单' . $this->gift->order_no;
            \Yii::$app->currency->setUser($user)->integral
                ->refund((int)$this->gift->use_integral_num, $desc);
        }
        return $this;
    }

    protected function couponResume()
    {
        // 优惠券恢复
        if ($this->gift->use_user_coupon_id) {
            $userCoupon = UserCoupon::findOne(['id' => $this->gift->use_user_coupon_id]);
            $userCoupon->is_use = 0;
            $userCoupon->save();
        }

        return $this;
    }

    protected function goodsAddStock()
    {
        /* @var GiftSendOrderDetail[] $orderDetail */
        $orderDetail = $this->gift->detail;
        $goodsAttrIdList = [];
        $goodsNum = [];
        foreach ($orderDetail as $item) {
            $goodsInfo = \Yii::$app->serializer->decode($item->goods_info);
            $goodsAttrIdList[] = $goodsInfo['goods_attr']['id'];
            $goodsNum[$goodsInfo['goods_attr']['id']] = $item->num;
        }
        $goodsAttrList = GoodsAttr::find()->where(['id' => $goodsAttrIdList])->all();
        /* @var GoodsAttr[] $goodsAttrList */
        foreach ($goodsAttrList as $goodsAttr) {
            $goodsAttr->updateStock($goodsNum[$goodsAttr->id], 'add');
        }

        return $this;
    }

    /**
     * 订单状态取消
     * @return $this
     * @throws \Exception
     */
    protected function orderCancel()
    {
        CommonGift::setOrder($this->gift, GiftLog::findOne($this->gift_id), 1,0);
        return $this;
    }
}
