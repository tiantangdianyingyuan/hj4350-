<?php


namespace app\plugins\community\jobs;


use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderSendForm;
use app\forms\common\order\send\NoExpressSendForm;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\community\handlers\HandlerRegister;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ActivityJob extends BaseObject implements JobInterface
{
    public $mall;
    public $appVersion;

    public $user_id;

    public $activity_id;

    public function execute($queue)
    {
        \Yii::error('==========================社区团购活动自动到期队列=========================');
        \Yii::error($this->user_id);
        \Yii::$app->user->setIdentity(User::findOne($this->user_id));
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $activity = CommunityActivity::findOne($this->activity_id);
            if (empty($activity)) {
                throw new \Exception('社区团购活动不存在ID-' . $this->activity_id);
            }
            \Yii::error('社区团购活动开始执行到期任务ID-' . $this->activity_id);
            switch ($activity['condition']) {
                case 1:
                    $condition_info = CommunityOrder::find()->alias('co')
                        ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                        ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                        ->andWhere(['co.activity_id' => $activity['id'], 'co.is_delete' => 0])
                        ->groupBy('co.middleman_id')->select('count(distinct co.user_id) as num,middleman_id')->asArray()->all();
                    break;
                case 2:
                    $condition_info = CommunityOrder::find()->alias('co')
                        ->andWhere(['co.activity_id' => $activity['id'], 'co.is_delete' => 0])
                        ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                        ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                        ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                        ->andWhere(['od.is_delete' => 0])
                        ->groupBy('co.middleman_id')->select('sum(od.num) num,co.middleman_id')->asArray()->all();
                    break;
                default:
                    $condition_info = CommunityOrder::find()->andWhere(['activity_id' => $activity['id'], 'is_delete' => 0])
                        ->groupBy('middleman_id')->asArray()->all();
            }
            foreach ($condition_info as $value) {
                \Yii::error('团长ID:' . $value['middleman_id'] . '下订单开始执行');

                $is_success = 0;
                switch ($activity['condition']) {
                    case 0:
                        \Yii::error('活动成功');
                        $is_success = 1;
                        break;
                    case 1 || 2:
                        if ($value['num'] >= $activity['num']) {
                            \Yii::error('活动成功');
                            $is_success = 1;
                        }
                        break;
                    default:
                        \Yii::error('活动失败');
                        break;
                }
                //一键锁定成团判断
                if (!empty(CommunityActivityLocking::findOne(['middleman_id' => $value['middleman_id'], 'activity_id' => $activity['id'], 'is_delete' => 0]))) {
                    \Yii::error('锁定成团');
                    $is_success = 1;
                }
                //失败退款，成功改状态
                $order_info = CommunityOrder::find()->andWhere(['middleman_id' => $value['middleman_id'], 'activity_id' => $activity['id'], 'is_delete' => 0])
                    ->with(['detail', 'order'])->all();
                if ($is_success == 0) {
                    foreach ($order_info as $item) {
                        $form = new OrderCancelForm();
                        $form->order_id = $item->order_id;
                        $form->status = 1;
                        $form->remark = '社区团购活动失败取消订单';
                        $re = $form->save();
                        if ($re['code'] == ApiCode::CODE_SUCCESS) {
                            \Yii::error('order_id:' . $item->order_id . ' 自动取消退款');
                        }
                    }
                } elseif ($is_success == 1) {
                    foreach ($order_info as $item) {
                        if ($item->order->is_pay == 0 || $item->order->cancel_status != 0) {
                            \Yii::error('订单ID-' . $item['order_id'] . '未付款或正在取消');
                            continue;
                        }

                        $form = new NoExpressSendForm();
                        $form->order_id = $item->order_id;
                        $form->express_content = '社区团购活动自动发货';
                        $form->mch_id = 0;
                        foreach ($item->detail as $ditem) {
                            $form->order_detail_id[] = $ditem->id;
                        }
                        $form->is_trigger_event = false;
                        $re = $form->send();
                        if ($re['code'] == ApiCode::CODE_SUCCESS) {
                            \Yii::error('order_id:' . $item->order_id . ' 自动发货');
                        }
                        \Yii::$app->trigger(HandlerRegister::COMMUNITY_SUCCESS, new OrderEvent([
                            'order' => $item->order
                        ]));
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception->getMessage());
            \Yii::error($exception);
        }
    }
}
