<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/24
 * Time: 14:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\models\Order;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonForm;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\PickUpTemplate;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityMiddlemanActivity;
use app\plugins\community\models\CommunityOrder;

class NoticeForm extends Model
{
    public $activity_id;

    public function rules()
    {
        return [
            ['activity_id', 'required']
        ];
    }

    public function notice()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonMiddleman = CommonMiddleman::getCommon();
            $middleman = $commonMiddleman->getConfig(\Yii::$app->user->id);
            if (!$middleman || $middleman->status != 1) {
                throw new \Exception('不是团长不允许请求接口');
            }
            $activity = CommonActivity::getActivity($this->activity_id);
            if (!$activity) {
                throw new \Exception('活动不存在');
            }
            $remind = $commonMiddleman->getRemind($middleman, $activity->id);
            if ($remind) {
                throw new \Exception('已经通知不需要重复通知');
            }
            /* @var CommunityOrder[] $orderList */
            $orderIdList = Order::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_pay' => 1, 'cancel_status' => 0,
                ])->select('id');
            $orderList = CommunityOrder::find()->with(['user', 'order.detail'])
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'middleman_id' => $middleman->user_id,
                    'activity_id' => $this->activity_id, 'is_delete' => 0, 'order_id' => $orderIdList
                ])->all();
            if (empty($orderList)) {
                throw new \Exception('该活动没有用户下单，不需要通知');
            }
            switch ($activity->condition) {
                case 1:
                    $count = count(array_reduce($orderList, function ($temp, $order) {
                        if (!in_array($order->user_id, $temp)) {
                            $temp[] = $order->user_id;
                        }
                        return $temp;
                    }, []));
                    $flag = $count >= $activity->num;
                    break;
                case 2:
                    $count = array_reduce($orderList, function ($num, $order) {
                        $num += array_reduce($order->order->detail, function ($num1, $detail) {
                            $num1 += $detail->num;
                            return $num1;
                        }, 0);
                        return $num;
                    }, 0);
                    $flag = $count >= $activity->num;
                    break;
                default:
                    $flag = true;
            }
            if (strtotime($activity->end_at) > time() || !$flag) {
                throw new \Exception('活动未成功，请在活动成功之后，再进行通知');
            }
            foreach ($orderList as $order) {
                $template = new PickUpTemplate([
                    'orderNo' => $order->order->order_no,
                    'pickUpNo' => CommonForm::setNum($order->no),
                    'address' => $middleman->address->province . $middleman->address->city .
                        $middleman->address->district . $middleman->address->detail,
                    'page' => 'plugins/community/order-detail/order-detail?is_user=1&id=' . $order->order_id,
                ]);
                $template->user = $order->user;
                $template->send();
            }
            $model = new CommunityMiddlemanActivity();
            $model->middleman_id = $middleman->user_id;
            $model->activity_id = $activity->id;
            $model->is_remind = 1;
            $model->is_delete = 0;
            $model->save();
            return $this->success(['msg' => '提醒成功']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
