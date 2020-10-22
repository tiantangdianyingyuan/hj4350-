<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/15
 * Time: 11:58
 */


namespace app\plugins\advance\jobs;


use app\forms\api\order\OrderException;
use app\models\Mall;
use app\models\User;
use app\plugins\advance\forms\common\MsgService;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\TailMoneyTemplate;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AdvanceRemindJob extends BaseObject implements JobInterface
{
    public $id;
    /**@var AdvanceGoods $goods**/
    public $goods;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        try {
            $order = AdvanceOrder::findOne(['id' => $this->id]);
            if (!$order) {
                throw new OrderException('定金订单不存在。');
            }
            $mall = Mall::findOne(['id' => $order->mall_id]);
            \Yii::$app->setMall($mall);
            $user = User::findOne(['id' => $order->user_id, 'mall_id' => $order->mall_id, 'is_delete' => 0]);
            if ($order->is_cancel == 0 && $order->is_delete == 0 && $order->is_refund == 0 && $order->is_pay == 1) {
                if ($this->goods->pay_limit == -1) {
                    $endTime = '无限制';
                } else {
                    $endTime = date('Y-m-d H:i:s',strtotime($this->goods->end_prepayment_at) + $this->goods->pay_limit*24*60*60);
                }

                //尾款计算
                $orderModel = AdvanceOrder::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_id' => $this->goods->goods_id,
                    'is_pay' => 1,
                    'is_cancel' => 0,
                    'is_refund' => 0,
                    'is_delete' => 0
                ]);
                $advanceOrderCount = $orderModel->count();
                $discount = 10;//初始10折，等于没有优惠折扣
                $rules = json_decode($this->goods->ladder_rules,true);
                if (is_array($rules)) {
                    foreach ($rules as $value) {
                        if ($advanceOrderCount >= $value['num']) {
                            $discount = $value['discount'];
                        }
                    }
                }
                $goodsInfo = json_decode($order->goods_info, true);
                $price = $goodsInfo['goods_attr']['price'];

                $price = bcmul(bcsub(bcdiv(bcmul($price, $discount), 10), $order->swell_deposit), $order->goods_num);//先阶梯折扣，后膨胀金优惠，再乘以数量
                $price = ($price < 0) ? 0 : $price;

                $tplMsg = new TailMoneyTemplate([
                    'page' => 'plugins/advance/order/order',
                    'user' => $user,
                    'goodsName' => $this->goods->goods->goodsWarehouse->name,
                    'price' => price_format($price),
                ]);
                $tplMsg->send();

                MsgService::sendSms($user,$this->goods->goods->goodsWarehouse->name);
            }
        } catch (\Exception $exception) {
            \Yii::error("开始预售提醒失败:" . $exception->getMessage() . $exception->getFile() . $exception->getLine());
        }
    }
}