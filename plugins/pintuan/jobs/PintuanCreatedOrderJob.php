<?php

namespace app\plugins\pintuan\jobs;

use app\forms\common\template\TemplateSend;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\pintuan\forms\common\PintuanFailTemplate;
use app\plugins\pintuan\forms\common\PintuanSuccessTemplate;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\step\models\GoodsAttr;
use app\plugins\wxapp\models\WxappTemplate;
use yii\base\Component;
use yii\queue\JobInterface;

class PintuanCreatedOrderJob extends Component implements JobInterface
{
    public $pintuan_order_id;

    public function execute($queue)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $pintuanOrder = PintuanOrders::findOne($this->pintuan_order_id);
        try {
            \Yii::warning('拼团定时任务更新状态开始');
            if (!$pintuanOrder) {
                throw new \Exception('拼团组订单不存在');
            }
            \Yii::warning('正在执行的拼团订单ID:' . $pintuanOrder->id);
            \Yii::$app->setMall(Mall::findOne($pintuanOrder->mall_id));
            // 未拼团成功的
            if ($pintuanOrder->status == 1 || $pintuanOrder->status == 4) {
                $pintuanOrder->status = 3;
                $res = $pintuanOrder->save();
                if (!$res) {
                    throw new \Exception((new Model())->getErrorMsg($pintuanOrder));
                }

                $list = PintuanOrderRelation::find()->where([
                    'pintuan_order_id' => $pintuanOrder->id,
                    'is_delete' => 0
                ])->with('order.orderDetail.goodsWarehouse')->all();

                /** @var PintuanOrderRelation[] $list */
                foreach ($list as $item) {
                    // 如果是机器人则跳过
                    if ($item->robot_id > 0) {
                        continue;
                    }
                    // 将未拼团成功的订单改为取消状态
                    if (!$item->order) {
                        \Yii::error('未拼团成功订单号不存在:' . $item->order_id);
                    }

                    $user = User::findOne(['id' => $item->user_id]);
                    // 判断订单是否取消，为防止订单又积分、余额重复退
                    if ($item->order->cancel_status != 1) {
                        // 加入订单回收站且删除的订单不进行退积分、优惠券
                        if ($item->order->is_delete == 0) {
                            // 用户积分恢复
                            if ($item->order->use_integral_num) {
                                $desc = '商品订单取消，订单号' . $item->order->order_no;
                                $customDesc = \Yii::$app->serializer->encode($item->order);
                                \Yii::$app->currency->setUser($user)->integral->add(
                                    (int)$item->order->use_integral_num,
                                    $desc,
                                    $customDesc,
                                    $item->order->order_no
                                );
                            }

                            // 优惠券恢复
                            if ($item->order->use_user_coupon_id) {
                                UserCoupon::updateAll(['is_use' => 0], ['id' => $item->order->use_user_coupon_id]);
                            }

                            // 库存退回
                            foreach ($item->order->detail as $dItem) {
                                $goodsInfo = \Yii::$app->serializer->decode($dItem->goods_info);
                                $pintuanGoodsAttr = PintuanGoodsAttr::findOne([
                                    'pintuan_goods_groups_id' => $pintuanOrder->pintuan_goods_groups_id,
                                    'goods_id' => $dItem->goods_id,
                                    'goods_attr_id' => $goodsInfo->goods_attr['id']
                                ]);

                                $pintuanGoodsAttr->pintuan_stock += $dItem->num;
                                if (!$pintuanGoodsAttr->save()) {
                                    throw new \Exception((new Model())->getErrorMsg($pintuanGoodsAttr));
                                }
                            }
                        }

                        $item->order->cancel_status = 1;
                        $item->order->cancel_time = mysql_timestamp();
                        $item->order->seller_remark = '拼团失败,订单状态更新为取消';
                        $item->order->status = 1;
                        $res = $item->order->save();
                        if (!$res) {
                            \Yii::error('未拼团成功订单状态更新失败,订单号:' . $item->order->order_no);
                        }

                        // 加入订单回收站且删除的订单不进行退款
                        if ($item->order->is_pay == 1 && $item->order->is_delete == 0) {

                            // 已付款就退款
                            \Yii::$app->payment->refund($item->order->order_no, $item->order->total_pay_price);

                            $this->sendTemplateMsg($item);
                        }
                    }
                }
                $transaction->commit();
                \Yii::warning('拼团失败,订单退款完成');
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            // 如果退款失败，则重新创建定时任务
            \Yii::warning('拼团退款失败,重新创建拼团退款任务');
            \Yii::$app->queue->delay(3600)
                ->push(new PintuanCreatedOrderJob([
                    'pintuan_order_id' => $this->pintuan_order_id,
                ]));

            if ($pintuanOrder->status != 4) {
                $pintuanOrder->status = 4;
                $res = $pintuanOrder->save();
                \Yii::warning('拼团订单(' . $pintuanOrder->id . ')状态更新为未退款');
            }
            \Yii::warning($e);
        }
    }

    /**
     * 拼团失败订阅消息
     * @param PintuanOrderRelation $item
     * @throws \Exception
     */
    private function sendTemplateMsg($item)
    {
        try {
            $user = User::findOne($item->user_id);
            if (!$user) {
                throw new \Exception('用户不存在！,拼团失败订阅消息发送失败');
            }

            $goodsName = '';
            /** @var OrderDetail $dItem */
            foreach ($item->order->detail as $dItem) {
                $goodsName .= $dItem->goods->getName();
            }

            $pintuanFailTemplate = new PintuanFailTemplate([
                'order_no' => $item->order->order_no,
                'goodsName' => $goodsName,
                'remark' => '拼团人数不足'
            ]);


            $pintuanFailTemplate->page = 'plugins/pt/detail/detail?id=' . $item->pintuan_order_id;
            $pintuanFailTemplate->user = $user;
            $res = $pintuanFailTemplate->send();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }
}
