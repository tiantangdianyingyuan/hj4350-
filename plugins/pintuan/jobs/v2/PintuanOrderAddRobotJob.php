<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\jobs\v2;


use app\models\Mall;
use app\models\Model;
use app\plugins\pintuan\forms\common\v2\PintuanSuccessForm;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\models\PintuanRobots;
use yii\base\Component;
use yii\queue\JobInterface;

class PintuanOrderAddRobotJob extends Component implements JobInterface
{
    public $pintuan_order_id;
    public $robot_id;

    public function execute($queue)
    {
        \Yii::warning('拼团组添加机器人操作开始');
        $transaction = \Yii::$app->db->beginTransaction();
        /** @var PintuanOrders $pintuanOrder */
        $pintuanOrder = PintuanOrders::findOne($this->pintuan_order_id);
        try {
            if (!$pintuanOrder) {
                throw new \Exception('拼团组订单不存在');
            }
            \Yii::$app->setMall(Mall::findOne($pintuanOrder->mall_id));
            // TODO 判断拼团时间
            // 拼团中的
            if ($pintuanOrder->status == 1) {
                $robot = PintuanRobots::findOne([
                    'id' => $this->robot_id,
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0
                ]);
                if (!$robot) {
                    throw new \Exception('机器人不存在：' . $this->robot_id);
                }

                $pintuanOrderRelation = new PintuanOrderRelation();
                $pintuanOrderRelation->order_id = 0;
                $pintuanOrderRelation->user_id = 0;
                $pintuanOrderRelation->pintuan_order_id = $this->pintuan_order_id;
                $pintuanOrderRelation->is_parent = 0;
                $pintuanOrderRelation->is_groups = 1;
                $pintuanOrderRelation->robot_id = $this->robot_id;
                $res = $pintuanOrderRelation->save();
                if (!$res) {
                    throw new \Exception((new Model())->getErrorMsg($pintuanOrderRelation));
                }

                $pintuanSuccessForm = new PintuanSuccessForm();
                $pintuanSuccessForm->pintuanOrder = $pintuanOrder;
                $pintuanSuccessForm->updateOrder();

                // 拼团参与人数大于 拼团所需人数
                if ($pintuanSuccessForm->orderCount > $pintuanOrder->people_num) {
                    throw new \Exception('添加拼团机器人并发处理');
                }
            }
            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e);
        }
    }
}