<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/13
 * Time: 11:06
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\core\Pagination;
use app\models\OrderDetail;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityOrder;
use app\plugins\community\models\Order;

class RefundListForm extends Model
{
    public $activity_id;
    public $middleman_id;
    public $page;

    public function rules()
    {
        return [
            [['activity_id', 'middleman_id'], 'required'],
            [['activity_id', 'middleman_id', 'page'], 'integer'],
            ['page', 'default', 'value' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'activity_id' => '指定活动id'
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->checkActivity()) {
                return $this->fail(['msg' => '活动尚未失败，无法退款']);
            }
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
        $orderIds = CommunityOrder::find()
            ->where([
                'activity_id' => $this->activity_id, 'middleman_id' => $this->middleman_id, 'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])->select('order_id');
        /** @var Order[] $list */
        $list = Order::find()->with('user')
            ->where(['id' => $orderIds, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'is_pay' => 1])
            ->page($pagintaion, 20, $this->page)
            ->all();
        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'nickname' => $item->user->nickname,
                'avatar' => $item->user->userInfo->avatar,
                'total_pay_price' => $item->total_pay_price,
                'status' => $item->cancel_status == 1 ? '成功' : '失败'
            ];
        }
        return $this->success(['list' => $newList, 'pagination' => $pagintaion]);
    }

    public function refund()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->checkActivity()) {
                throw new \Exception('活动尚未失败，无法退款');
            }
            $orderIds = CommunityOrder::find()
                ->where([
                    'activity_id' => $this->activity_id, 'middleman_id' => $this->middleman_id, 'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id
                ])->select('order_id');
            /**
             * @var Order[] $list
             * @var Pagination $pagintaion
             */
            $list = Order::find()->with('user')
                ->where([
                    'id' => $orderIds, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'is_pay' => 1,
                    'is_send' => 0
                ])
                ->andWhere(['!=', 'cancel_status', 1])
                ->page($pagintaion)
                ->all();
            foreach ($list as $item) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $item->cancel_status = 1;
                    $item->cancel_time = mysql_timestamp();
                    if (!$item->save()) {
                        throw new \Exception($this->getErrorMsg($item));
                    }
                    \Yii::$app->payment->refund($item->order_no, $item->total_pay_price);
                    $transaction->commit();
                } catch (\Exception $exception) {
                    $transaction->rollBack();
                    throw $exception;
                }
            }
            if ($pagintaion->page_count >= 2) {
                return $this->success(['retry' => 1]);
            } else {
                return $this->success(['msg' => '退款成功']);
            }
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    protected function checkActivity()
    {
        $activity = CommunityActivity::findOne(['id' => $this->activity_id, 'is_delete' => 0]);
        if (!$activity) {
            throw new \Exception('活动不存在，或已删除');
        }
        if (strtotime($activity->end_at) > time()) {
            throw new \Exception('活动尚未结束，无法发起退款');
        }
        $success = false;
        $isLock = CommunityActivityLocking::findOne([
            'middleman_id' => $this->middleman_id, 'activity_id' => $this->activity_id, 'is_delete' => 0
        ]);
        if ($isLock) {
            $success = true;
        }
        switch ($activity->condition) {
            case 0:
                $success = true;
                break;
            case 1:
                $orderIds = CommunityOrder::find()
                    ->where([
                        'activity_id' => $this->activity_id, 'middleman_id' => $this->middleman_id, 'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id
                    ])->select('order_id');
                $count = Order::find()->with('user')
                    ->where([
                        'id' => $orderIds, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'is_pay' => 1, 'is_recycle' => 0
                    ])->andWhere(['!=', 'cancel_status', 1])->groupBy('user_id')->count();
                if ($activity->num < $count) {
                    $success = true;
                }
                break;
            case 2:
                $count = CommunityOrder::find()->alias('co')
                        ->andWhere(['co.middleman_id' => $this->middleman_id, 'co.activity_id' => $this->activity_id, 'co.is_delete' => 0])
                        ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                        ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                        ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                        ->andWhere(['od.is_delete' => 0])
                        ->sum('od.num') ?? 0;
                if ($activity->num < $count) {
                    $success = true;
                }
                break;
            default:
        }
        if ($success) {
            throw new \Exception('活动成功，无法退款');
        }
        return true;
    }
}
