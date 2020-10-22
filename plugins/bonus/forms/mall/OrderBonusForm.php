<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/3
 * Email: <657268722@qq.com>
 */

namespace app\plugins\bonus\forms\mall;

use app\models\Model;
use app\models\Order;
use app\plugins\bonus\events\MemberEvent;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainRelation;
use app\plugins\bonus\models\BonusCashLog;
use app\plugins\bonus\models\BonusMembers;
use app\plugins\bonus\models\BonusOrderLog;
use app\plugins\bonus\models\BonusSetting;
use yii\db\Exception;

class OrderBonusForm extends Model
{
    /** @var Order */
    public $order;

    private $STATUS_EXPECT = 0;
    private $STATUS_FAIL = 2;
    private $STATUS_SUCCESS = 1;

    private $bonus_rate = 0;//分红比例

    public function rules()
    {
        return [
            [['order'], 'required'],
        ];
    }


    /**
     * 预分红增加
     * @throws Exception
     * @throws \HttpResponseException
     */
    public function bonusAdd()
    {
        if (!$this->validate()) {
            throw new \HttpResponseException('预分红增加-缺失参数');
        }
        \Yii::error('团队预分红开始，order_id：' . $this->order->id);
        //判断是否有队长
        $info = BonusCaptainRelation::findOne(['user_id' => $this->order->user_id]);
        if (empty($info)) {
            \Yii::error('没有队长，不分红');
            return;
        }
        //判断是否已分红过
        $bonus_info = BonusOrderLog::findOne(['order_id' => $this->order->id, 'is_delete' => 0]);
        if (!empty($bonus_info)) {
            \Yii::error('该订单已分红');
            return;
        }
        $captain_info = \app\plugins\bonus\forms\common\CommonForm::captain($info->captain_id);
        if (empty($captain_info)) {
            throw new \HttpResponseException('预分红增加-获取分红比例失败');
        }
        $this->bonus_rate = $captain_info['bonus_rate'] / 100;
        $status = $this->STATUS_EXPECT;
        $immediately = false;
        if (count($this->order->detail) > 0 && ($this->order->detail['0']->goods_type == 'ecard' || $this->order->detail['0']->goods_type == 'exchange')) {
            $status = $this->STATUS_SUCCESS;
            $immediately = true;
        }
        if ($this->order->sign == 'vip_card' || ($this->order->sign == 'gift' && $this->order->auto_cancel_time == '' && $this->order->is_sale == 1)) {
            $status = $this->STATUS_SUCCESS;
            $immediately = true;
        }
        $model = new BonusOrderLog();
        $model->mall_id = \Yii::$app->mall->id;
        $model->order_id = $this->order->id;
        $model->from_user_id = $this->order->user_id;
        $model->to_user_id = $info->captain_id;//队长
        $model->price = $this->order->total_goods_price;//订单商品总金额
        $model->bonus_price = bcmul($this->order->total_goods_price, $this->bonus_rate);
        $model->bonus_rate = (string)($this->bonus_rate * 100);
        $model->status = $status;
        if (!$model->save()) {
            throw new Exception('预分红增加-分红状态失败');
        }
        //增加预分红
        if (!$this->expectBonus($model->to_user_id, $model->bonus_price)) {
            throw new Exception('预分红增加-增加预分红' . $model->to_user_id . '---' . $model->bonus_price);
        }

        if ($immediately) {
            $this->bonusOver();
        }
    }

    /**
     * 预分红取消
     * @throws Exception
     * @throws \HttpResponseException
     */
    public function bonusDel()
    {
        if (!$this->validate()) {
            throw new \HttpResponseException('预分红取消-缺失参数');
        }
        $model = BonusOrderLog::findOne(['order_id' => $this->order->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        if (empty($model)) {
            return;
        }
        $model->fail_bonus_price = $model->bonus_price;
        $model->bonus_price = 0;
        $model->status = $this->STATUS_FAIL;
        if (!$model->save()) {
            throw new Exception('预分红取消-分红状态失败');
        }
        //减少预分红
        if (!$this->expectBonus($model->to_user_id, 0 - $model->fail_bonus_price)) {
            throw new Exception('分红取消-减少预分红');
        }
    }


    /**
     * 售后退款订单减分红
     * @throws Exception
     * @throws \HttpResponseException
     */
    public function bonusCut()
    {
        //该方法调用$this->order是order_refund
        if (!$this->validate()) {
            throw new \HttpResponseException('售后退款订单减分红-缺失参数');
        }
        //判断是否有分红订单\
        $model = BonusOrderLog::findOne(['order_id' => $this->order->order_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        if (empty($model)) {
            return;
        }
        $cut_price = $model->bonus_price;
        $model->bonus_price -= bcmul($this->order->refund_price, $model->bonus_rate / 100);//根据退款减分红金额
        $cut_price -= $model->bonus_price;//原来的分红 - 现在的分红 = 少的分红
        if ($model->bonus_price == 0) {
            $model->status = $this->STATUS_FAIL;
        }
        $model->fail_bonus_price += $cut_price;//失败的分红，即退款部分分红
        if (!$model->save()) {
            throw new Exception('售后退款订单减分红');
        }
        //减少队长预分红金额
        if (!$this->expectBonus($model->to_user_id, 0 - $cut_price)) {
            throw new Exception('减少队长预分红金额');
        }
    }


    /**
     * 分红完成
     * @throws Exception
     * @throws \HttpResponseException
     */
    public function bonusOver()
    {
        if (!$this->validate()) {
            throw new \HttpResponseException('分红完成-缺失参数');
        }

        $model = BonusOrderLog::findOne(['order_id' => $this->order->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        //跳过空分红订单和失败的分红订单
        if (empty($model) || $model->status == $this->STATUS_FAIL) {
            return;
        }
        if (!BonusOrderLog::updateAll(['status' => $this->STATUS_SUCCESS], ['order_id' => $this->order->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])) {
            \Yii::error('分红完成-分红状态失败(兑换中心，超级会员卡，礼物除外)');
        }
        //减少预分红
        if (!$this->expectBonus($model->to_user_id, 0 - $model->bonus_price)) {
            throw new Exception('分红完成-减少预分红');
        }
        //增加流水
        if (!CommonForm::cashLog($model->to_user_id, $model->bonus_price, 1, '分红收入')) {
            throw new Exception('分红完成-增加流水');
        }
        //增加总分红
        if (!$this->totalBonus($model->to_user_id, $model->bonus_price)) {
            throw new Exception('分红完成-增加总分红');
        }
        //分红成功触发队长升级事件
        $event = new MemberEvent([
            'captain' => BonusCaptain::findOne(['user_id' => $model->to_user_id, 'mall_id' => \Yii::$app->mall->id])
        ]);
        \Yii::$app->trigger(BonusMembers::UPDATE_LEVEL, $event);
    }


    //预分红金额变动
    protected function expectBonus($captain_id, $price)
    {
        if ($price == 0) {
            return true;
        }
        return BonusCaptain::updateAllCounters(['expect_bonus' => $price], ['user_id' => $captain_id]);
    }

    //总分红变动
    protected function totalBonus($captain_id, $price)
    {
        if ($price == 0) {
            return true;
        }
        return BonusCaptain::updateAllCounters(['total_bonus' => $price, 'all_bonus' => $price], ['user_id' => $captain_id]);
    }
}
