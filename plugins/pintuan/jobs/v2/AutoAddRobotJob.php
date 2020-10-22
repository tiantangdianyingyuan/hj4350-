<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\jobs\v2;

use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\models\PintuanRobots;
use yii\base\Component;
use yii\queue\JobInterface;

class AutoAddRobotJob extends Component implements JobInterface
{
    public $pintuan_order_id;

    public function execute($queue)
    {
        \Yii::warning('自动添加机器人执行开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var PintuanOrders $pintuanOrder */
            $pintuanOrder = PintuanOrders::find()->where(['id' => $this->pintuan_order_id])->with('goods.pintuanGoods', 'orderRelation')->one();
            if (!$pintuanOrder) {
                throw new \Exception('拼团订单不存在');
            }
            // 拼团中的订单才执行
            if ($pintuanOrder->status == 1 && $pintuanOrder->goods->pintuanGoods->is_auto_add_robot == 1) {
                $robots = PintuanRobots::find()->where(['mall_id' => $pintuanOrder->mall_id, 'is_delete' => 0])->all();
                $robotIds = [];
                /** @var PintuanOrderRelation $orItem */
                foreach ($pintuanOrder->orderRelation as $orItem) {
                    if ($orItem->robot_id > 0) {
                        $robotIds[] = $orItem->robot_id;
                    }
                }
                /** @var PintuanRobots $robot */
                foreach ($robots as $robot) {
                    // 自动添加机器人时不能重复 手动添加时可重复
                    if (!in_array($robot->id, $robotIds)) {
                        \Yii::$app->queue->delay(0)->push(new PintuanOrderAddRobotJob([
                            'pintuan_order_id' => $this->pintuan_order_id,
                            'robot_id' => $robot->id,
                        ]));
                        break;
                    }
                }

                // TODO 有个问题 如果编辑商品更改了机器人设置 这里会受影响
                // 再次添加队列任务
                $pintuanGoods = $pintuanOrder->goods->pintuanGoods;
                if ($pintuanGoods->add_robot_time > 0 && $pintuanGoods->is_auto_add_robot == 1) {
                    \Yii::$app->queue->delay($pintuanGoods->add_robot_time * 60)->push(new AutoAddRobotJob([
                        'pintuan_order_id' => $this->pintuan_order_id,
                    ]));
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getLine() . $e->getMessage());
        }
        \Yii::warning('自动添加机器人执行结束');
    }
}