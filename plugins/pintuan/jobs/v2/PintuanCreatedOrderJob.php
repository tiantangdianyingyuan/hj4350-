<?php

namespace app\plugins\pintuan\jobs\v2;

use app\forms\common\ecard\CommonEcard;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserIdentity;
use app\plugins\pintuan\forms\common\v2\PintuanFailTemplate;
use app\plugins\pintuan\forms\common\v2\PintuanSuccessForm;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

class PintuanCreatedOrderJob extends Component implements JobInterface
{
    public $pintuan_order_id;

    public function execute($queue)
    {
        $this->autoSuccess();

        /** @var PintuanOrders $pintuanOrder */
        $pintuanOrder = PintuanOrders::find()->andWhere(['id' => $this->pintuan_order_id])->with('goods')->one();

        try {
            if (!$pintuanOrder) {
                throw new \Exception('拼团订单不存在');
            }
            \Yii::$app->setMall(Mall::findOne($pintuanOrder->mall_id));
            // 未拼团成功的
            if ($pintuanOrder->status == 1 || $pintuanOrder->status == 4) {
                \Yii::warning('拼团定时任务更新状态开始,订单ID:' . $pintuanOrder->id);
                $pintuanOrder->status = 3;
                $res = $pintuanOrder->save();
                if (!$res) {
                    throw new \Exception((new Model())->getErrorMsg($pintuanOrder));
                }

                $list = PintuanOrderRelation::find()->where(['pintuan_order_id' => $pintuanOrder->id, 'is_delete' => 0])
                    ->with('order.orderDetail.goodsWarehouse', 'user')
                    ->all();

                $commonEcard = CommonEcard::getCommon();
                /** @var PintuanOrderRelation[] $list */
                foreach ($list as $item) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // 如果是机器人则跳过
                        if ($item->robot_id > 0) {
                            continue;
                        }

                        if (!$item->user) {
                            throw new \Exception('用户不存在');
                        }
                        // 判断订单是否取消，为防止订单积分、优惠券、余额重复退
                        if ($item->order->cancel_status == 1 || $item->order->is_delete == 1) {
                            \Yii::warning('拼团订单已取消或已删除');
                            continue;
                        }
                        // 拼团不成功的返还卡密
                        $commonEcard->refundEcard([
                            'type' => 'order',
                            'order' => $item->order,
                        ]);

                        // 用户积分恢复
                        if ($item->order->use_integral_num) {
                            $desc = '商品订单取消，订单号' . $item->order->order_no;
                            $customDesc = \Yii::$app->serializer->encode($item->order);
                            \Yii::$app->currency->setUser($item->user)->integral->add(
                                (int) $item->order->use_integral_num,
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
                        /** @var OrderDetail $dItem */
                        foreach ($item->order->detail as $dItem) {
                            $goodsInfo = \Yii::$app->serializer->decode($dItem->goods_info);
                            $goodsAttr = GoodsAttr::findOne(['goods_id' => $dItem->goods_id, 'id' => $goodsInfo->goods_attr['id']]);
                            $goodsAttr->stock += $dItem->num;
                            if (!$goodsAttr->save()) {
                                throw new \Exception((new Model())->getErrorMsg($goodsAttr));
                            }
                        }

                        $item->order->cancel_status = 1;
                        $item->order->cancel_time = mysql_timestamp();
                        $item->order->seller_remark = '拼团失败,订单状态更新为取消';
                        $item->order->status = 1;
                        $res = $item->order->save();
                        if (!$res) {
                            throw new \Exception((new Model())->getErrorMsg($item->order));
                        }

                        $this->refund($item);
                        $transaction->commit();
                    } catch (\Exception $exception) {
                        $transaction->rollBack();
                        throw $exception;
                    }
                }

                \Yii::warning('拼团定时任务更新状态结束,订单ID:' . $pintuanOrder->id);
            }
        } catch (\Exception $e) {
            \Yii::warning('拼团定时任务更新状态出错,订单ID:' . $pintuanOrder->id);
            \Yii::warning('错误信息：' . $e->getLine() . '-' . $e->getMessage());
            $this->tryAgain($pintuanOrder);
        }
    }

    /**
     * 拼团订单失败退款 退款要放到最后处理 因为退款无法回滚
     * @param  [type] $item [description]
     * @return [type]       [description]
     */
    private function refund($item)
    {
        if ($item->order->is_recycle == 1) {
            \Yii::warning('订单加入回收站，无需退款');
            return false;
        }

        $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
        $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
        // 订单已退款
        if ($paymentRefund) {
            \Yii::warning('订单ID:' . $item->order->id . '已退款');
            return false;
        }

        if ($item->order->is_pay == 1) {
            // 已付款就退款
            $res = \Yii::$app->payment->refund($item->order->order_no, $item->order->total_pay_price);
            $this->sendTemplateMsg($item);
        }

        \Yii::warning('拼团自动退款执行完成');
        return true;
    }

    /**
     * 如果退款失败，则重新创建定时任务
     * @param PintuanOrders $pintuanOrder
     */
    private function tryAgain($pintuanOrder)
    {
        \Yii::warning('拼团退款失败,重新创建拼团退款任务,拼团订单ID:' . $pintuanOrder->id);
        \Yii::$app->queue->delay(3600)
            ->push(new PintuanCreatedOrderJob([
                'pintuan_order_id' => $this->pintuan_order_id,
            ]));

        // 更新订单退款状态
        if ($pintuanOrder->status != 4) {
            $pintuanOrder->status = 4;
            $pintuanOrder->save();
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
            if (!$item->user) {
                throw new \Exception('用户不存在,ID:' . $item->user_id);
            }

            $goodsName = '';
            /** @var OrderDetail $dItem */
            foreach ($item->order->detail as $dItem) {
                $goodsName .= $dItem->goods->getName();
            }

            $pintuanFailTemplate = new PintuanFailTemplate([
                'order_no' => $item->order->order_no,
                'goodsName' => $goodsName,
                'remark' => '拼团人数不足',
            ]);

            $pintuanFailTemplate->page = 'plugins/pt/detail/detail?id=' . $item->pintuan_order_id;
            $pintuanFailTemplate->user = $item->user;
            $res = $pintuanFailTemplate->send();
        } catch (\Exception $e) {
            \Yii::error('拼团订阅消息发送失败：' . $e->getMessage());
        }
    }

    /**
     * 自动添加机器人
     * @return bool
     */
    private function autoSuccess()
    {
        /** @var PintuanOrders $pintuanOrder */
        $pintuanOrder = PintuanOrders::find()->andWhere(['id' => $this->pintuan_order_id])->with('goods')->one();

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \Yii::warning('拼团自动添加机器人开始，订单ID:' . $pintuanOrder->id);

            // 判断是否需要自动成团
            if (!$pintuanOrder->goods) {
                throw new \Exception('拼团订单商品不存在');
            }

            if ($pintuanOrder->status != 1) {
                throw new \Exception('当前拼团状态为：' . $pintuanOrder->status . '无法继续添加机器人');
            }

            /** @var PintuanGoods $pintuanGoods */
            $pintuanGoods = PintuanGoods::find()->andWhere(['goods_id' => $pintuanOrder->goods->id])->with('goods')->one();

            if (!$pintuanGoods) {
                throw new \Exception('拼团商品不存在');
            }

            if (!$pintuanGoods->is_auto_add_robot) {
                throw new \Exception('拼团商品未开启自动添加机器人');
            }

            $count = User::find()->alias('u')
                ->andWhere(['u.mall_id' => $pintuanOrder->mall_id, 'u.is_delete' => 0])
                ->leftJoin(['ui' => UserIdentity::tableName()], 'u.id=ui.user_id')
                ->andWhere(['ui.is_super_admin' => 0, 'ui.is_admin' => 0, 'ui.is_operator' => 0])
                ->count();
            if ($count <= 0) {
                throw new \Exception('商城用户不足，无法添加机器人');
            }

            $orderRelation = PintuanOrderRelation::find()->andWhere(['pintuan_order_id' => $pintuanOrder->id, 'is_delete' => 0])->with('order')->all();
            $orCount = 0;
            foreach ($orderRelation as $key => $item) {
                if ($item->order->is_pay == 1 || $item->order->pay_type == 2) {
                    $orCount += 1;
                }
            }

            $needRobotCount = $pintuanOrder->people_num - $orCount;

            $limit = $needRobotCount;
            $count = $count > $limit ? floor($count / $limit) : 1;
            $page = rand(1, $count);

            $list = User::find()->alias('u')
                ->andWhere(['u.mall_id' => $pintuanOrder->mall_id, 'u.is_delete' => 0])
                ->leftJoin(['ui' => UserIdentity::tableName()], 'u.id=ui.user_id')
                ->andWhere(['ui.is_super_admin' => 0, 'ui.is_admin' => 0, 'ui.is_operator' => 0])
                ->with('userInfo')
                ->page($pagination, $limit, $page)
                ->all();

            if ($needRobotCount > count($list)) {
                throw new \Exception('商城用户数量不足拼团所需人数,所需人数:' . $needRobotCount . '商城用户人数:' . count($list));
            }
            $newList = $this->shuffleAssoc(ArrayHelper::toArray($list));

            $key = ['order_id', 'user_id', 'pintuan_order_id', 'is_parent', 'is_groups', 'robot_id', 'created_at'];
            $value = [];
            /** @var User $item */
            foreach ($newList as $item) {
                $value[] = [0, 0, $pintuanOrder->id, 0, 1, $item['id'], mysql_timestamp()];
            }

            $res = \Yii::$app->db->createCommand()->batchInsert(PintuanOrderRelation::tableName(), $key, $value)->execute();

            \Yii::warning('自动添加机器人数量:' . $res);
            \Yii::warning($value);

            $pintuanSuccessForm = new PintuanSuccessForm();
            $pintuanSuccessForm->pintuanOrder = $pintuanOrder;
            $pintuanSuccessForm->updateOrder();

            $transaction->commit();
            \Yii::warning('拼团自动添加机器人结束，订单ID:' . $pintuanOrder->id);
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::warning('拼团自动添加机器人出错，订单ID:' . $pintuanOrder->id);
            \Yii::warning('错误信息：' . $exception->getLine() . '-' . $exception->getMessage());
        }
    }

    private function shuffleAssoc($list)
    {
        if (!is_array($list)) {
            return $list;
        }

        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        }

        return $random;
    }
}
