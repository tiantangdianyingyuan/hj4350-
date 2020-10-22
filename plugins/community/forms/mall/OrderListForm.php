<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/11
 * Time: 13:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\models\Order;
use app\plugins\community\forms\export\OrderExport;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityOrder;

class OrderListForm extends Model
{
    public $page;
    public $date_start;
    public $date_end;
    public $activity_name;
    public $keyword_1;
    public $keyword;
    public $status;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['page', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            [['date_start', 'date_end', 'activity_name', 'keyword_1', 'keyword'], 'trim'],
            [['date_start', 'date_end', 'activity_name', 'keyword_1', 'keyword', 'flag'], 'string'],
            ['keyword_1', 'in', 'range' => ['order_no', 'user_id', 'middleman_name', 'middleman_mobile', 'mobile', 'name']],
            [['fields'], 'safe'],
            ['status', 'default', 'value' => -1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $condition = [];
        if ($this->activity_name !== '') {
            $activityIds = CommunityActivity::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere(['like', 'title', $this->activity_name])->select('id')
                ->column();
            $condition[] = ['activity_id' => $activityIds];
        }
        $orderCondition = [];
        $middlemanCondition = [];
        if ($this->keyword !== '') {
            switch ($this->keyword_1) {
                case 'order_no':
                    $orderCondition[] = ['like', 'order_no', $this->keyword];
                    break;
                case 'mobile':
                    $orderCondition[] = ['like', 'mobile', $this->keyword];
                    break;
                case 'name':
                    $orderCondition[] = ['like', 'name', $this->keyword];
                    break;
                case 'user_id': // 团长的用户id
                    $middlemanCondition = ['user_id' => $this->keyword];
                    break;
                case 'middleman_name':
                    $middlemanCondition = ['like', 'name', $this->keyword];
                    break;
                case 'middleman_mobile':
                    $middlemanCondition = ['like', 'mobile', $this->keyword];
                    break;
                default:
            }
        }
        if ($this->status !== '' && $this->status != null) {
            switch ($this->status) {
                case 0: // 待付款
                    $orderCondition[] = ['is_pay' => 0, 'cancel_status' => 0];
                    break;
                case 1: // 待发货
                    $orderCondition[] = ['is_pay' => 1, 'is_send' => 0, 'cancel_status' => 0];
                    break;
                case 2: // 待收货
                    $orderCondition[] = ['is_pay' => 1, 'is_send' => 1, 'is_confirm' => 0];
                    break;
                case 3: // 已收货
                    $orderCondition[] = ['is_confirm' => 1, 'is_sale' => 0];
                    break;
                case 4: // 已完成
                    $orderCondition[] = ['is_sale' => 1];
                    break;
                case 5: // 待处理
                    $orderCondition[] = ['cancel_status' => 2];
                    break;
                case 6: // 已取消
                    $orderCondition[] = ['cancel_status' => 1];
                    break;
                default:
            }
        }
        // 订单order相关筛选
        if (!empty($orderCondition)) {
            array_unshift($orderCondition, 'and');
            $orderIds = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere($orderCondition)->select('id')->column();
            $condition[] = ['order_id' => $orderIds];
        }
        // 团长community_middleman相关筛选
        if (!empty($middlemanCondition)) {
            $middlemanIds = CommunityMiddleman::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere($middlemanCondition)->select('user_id')
                ->column();
            $condition[] = ['middleman_id' => $middlemanIds];
        }
        // 筛选条件合并
        if (!empty($condition)) {
            array_unshift($condition, 'and');
        }
        /* @var CommunityOrder[] $list */
        $model = CommunityOrder::find()->with(['order.refund', 'middleman', 'activity'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($this->date_start, ['>=', 'created_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'created_at', $this->date_end])
            ->keyword(!empty($condition), $condition);
        if ($this->flag != 'EXPORT') {
            $model->page($pagination, 20, $this->page);
        }
        $list = $model->orderBy(['created_at' => SORT_DESC])
            ->all();
        $newList = [];
        foreach ($list as $item) {
            $newItem = [
                'order_no' => $item->order->order_no,
                'middleman_name' => $item->middleman->name,
                'middleman_mobile' => $item->middleman->mobile,
                'mobile' => $item->order->mobile,
                'name' => $item->order->user->nickname,
                'activity_name' => $item->activity->title,
                'pay_price' => $item->order->total_pay_price,
                'profit_price' => $item->profit_price > 0 ? $item->profit_price : 0,
                'is_pay' => $item->order->is_pay,
                'is_send' => $item->order->is_send,
                'is_confirm' => $item->order->is_confirm,
                'is_sale' => $item->order->is_sale,
                'cancel_status' => $item->order->cancel_status,
                'created_at' => $item->order->created_at,
                'id' => $item->order_id
            ];
            $newList[] = $newItem;
        }
        if ($this->flag == 'EXPORT') {
            $exp = new OrderExport();
            $exp->fieldsKeyList = explode(',', $this->fields);
            $exp->export($newList);
            return false;
        }
        return $this->success([
            'list' => $newList,
            'pagination' => $pagination,
            'export_list' => (new OrderExport())->fieldsList()
        ]);
    }
}
