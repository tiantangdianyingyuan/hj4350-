<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\controllers\mall;

use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\Controller;
use app\plugins\pintuan\forms\common\v2\PintuanSuccessForm;
use app\plugins\pintuan\forms\mall\OrderCancelForm;
use app\plugins\pintuan\forms\mall\OrderForm;
use app\plugins\pintuan\jobs\v2\PintuanCreatedOrderJob;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use yii\helpers\ArrayHelper;

class OrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
            $order = $res['data']['order'];
            if ($order['orderRelation']['pintuanOrder']['status'] == 1 ||
                $order['orderRelation']['pintuanOrder']['status'] == 3) {
                $order['is_send_show'] = 0;
                $order['is_cancel_show'] = 0;
                $order['is_clerk_show'] = 0;
            }
            $res['data']['order'] = $order;
            return $this->asJson($res);
        } else {
            return $this->render('detail');
        }
    }

    //清空回收站
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->destroyAll());
        }
    }

    public function actionOrderCancel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderCancelForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionCreateJob()
    {
        $isOk = \Yii::$app->request->get('isOk') ?: false;

        $orderNo = [];
        $orderIds = Order::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'order_no' => $orderNo])->select('id');
        $pintuanOrderRelation = PintuanOrderRelation::find()->andWhere(['order_id' => $orderIds])->all();
        $pintaunOrderIds = [];
        foreach ($pintuanOrderRelation as $key => $value) {
            $pintaunOrderIds[] = $value['pintuan_order_id'];
        }

        $pintuanOrders = PintuanOrders::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $pintaunOrderIds,
        ])
            ->with('orderRelation.order', 'orderRelation.pintuanOrder')
            ->all();

        $dataList = [];
        /** @var PintuanOrders $item */
        foreach ($pintuanOrders as $item) {
            /** @var PintuanOrderRelation $orItem */
            foreach ($item->orderRelation as $orItem) {
                if ($orItem->robot_id == 0 && $orItem->order->cancel_status != 1) {
                    $dataList[] = [
                        'order_no' => $orItem->order->order_no,
                        'pintuan_order_id' => $item->id,
                        'is_pay' => $orItem->order->is_pay,
                    ];
                }
            }
        }

        if ($isOk) {
            foreach ($dataList as $key => $item) {
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $item['order_no']])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                // 订单已退款
                if ($paymentRefund) {
                    \Yii::warning('订单ID:' . $item['order_no'] . '已退款');
                } else {
                    if ($item['is_pay'] == 1) {
                        try {
                            $this->autoSuccess($item['pintuan_order_id']);
                        } catch (\Exception $exception) {
                            \Yii::warning('拼团失败待退款订单改为拼团成功 失败，拼团订单号：' . $item['order_no'] . $exception->getMessage());
                        }
                    }
                }
            }

            array_unshift($dataList, ['is_ok' => true]);
        }
        dd($dataList);
    }

    /**
     * 自动添加机器人
     * @return bool
     */
    private function autoSuccess($pintuanOrderId)
    {
        /** @var PintuanOrders $pintuanOrder */
        $pintuanOrder = PintuanOrders::find()->andWhere(['id' => $pintuanOrderId])->with('goods')->one();

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

            // if (!$pintuanGoods) {
            //     throw new \Exception('拼团商品不存在');
            // }

            // if (!$pintuanGoods->is_auto_add_robot) {
            //     throw new \Exception('拼团商品未开启自动添加机器人');
            // }

            // if ($pintuanGoods->goods->status != 1) {
            //     throw new \Exception('拼团商品未上架');
            // }

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
            \Yii::warning('自动添加机器人数量' . $res);
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
            throw $exception;
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
