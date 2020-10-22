<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\community\forms\common\CommonForm;
use app\plugins\community\forms\export\ActivityDetailExport;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityOrder;
use app\plugins\community\models\CommunityRobots;
use app\plugins\community\models\Goods;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class ActivityDetailForm extends Model
{
    public $id;
    public $flag;
    public $fields;
    public $keyword;
    public $keyword_label;
    public $order_by = 'co.id';

    public $middleman_id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'middleman_id'], 'integer'],
            [['order_by', 'flag'], 'string'],
            [['fields'], 'safe'],
            [['keyword', 'keyword_label'], 'trim'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = ArrayHelper::toArray(CommunityActivity::findOne($this->id));
            if (empty($activity)) {
                throw new \Exception('活动不存在');
            }
            $activity['activity_status'] = CommonForm::timeSlot($activity);


            $order_query = CommunityMiddleman::find()->alias('cm')->where(['cm.mall_id' => \Yii::$app->mall->id, 'cm.status' => 1, 'cm.is_delete' => 0])
                ->leftJoin(['co' => CommunityOrder::tableName()], ['co.middleman_id' => new Expression('cm.user_id'), 'co.activity_id' => $this->id, 'co.is_delete' => 0]);
            if ($this->keyword) {
                $order_query->keyword($this->keyword_label == 1, ['cm.user_id' => $this->keyword])
                    ->keyword($this->keyword_label == 2, ['cm.user_id' => User::find()->andWhere(['and', ['like', 'nickname', $this->keyword], ['is_delete' => 0]])->select('id')])
                    ->keyword($this->keyword_label == 3, ['like', 'cm.name', $this->keyword])
                    ->keyword($this->keyword_label == 4, ['like', 'cm.mobile', $this->keyword]);
            }
            $order_query->leftJoin(['o' => Order::tableName()], ['co.order_id' => new Expression('o.id'), 'o.is_delete' => 0, 'o.is_recycle' => 0]);
            if ($this->flag != 'EXPORT') {
                $order_query->page($pagination);
            }
            $list = $order_query->with(['user.userInfo', 'address'])
                ->select('COALESCE(sum(`o`.`total_pay_price`),0) as `order_price`,count(`co`.`id`) as `order_num`,count(DISTINCT  `co`.`user_id`) as `user_num`,`cm`.`user_id`')
                ->groupBy('cm.user_id')
                ->orderBy($this->order_by)
                ->asArray()->all();
            foreach ($list as &$item) {
                $item['is_locking'] = CommunityActivityLocking::findOne(['activity_id' => $this->id, 'middleman_id' => $item['user_id'], 'is_delete' => 0]) ? 1 : -1;
                $item['condition_count'] = 0;
                $item['middleman']['nickname'] = $item['user']['nickname'];
                $item['middleman']['avatar'] = $item['user']['userInfo']['avatar'];
                $item['user_id'] = $item['user']['id'];
                unset($item['user']);

                switch ($activity['condition']) {
                    case 1:
                        $item['condition_count'] = $item['user_num'] ?? 0;
                        break;
                    case 2:
                        $item['condition_count'] = CommunityOrder::find()->alias('co')
                                ->andWhere(['co.middleman_id' => $item['user_id'], 'co.activity_id' => $this->id, 'co.is_delete' => 0])
                                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                                ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                                ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                                ->andWhere(['od.is_delete' => 0])
                                ->sum('od.num') ?? 0;
                        break;
                    default:
                        $item['condition_count'] = 0;
                }

                $item['is_success'] = 0;
                //锁定成团
                if ($item['is_locking'] == 1) {
                    $item['is_success'] = 1;
                }

                $item['is_refund'] = 1;
                if (strtotime($activity['start_at']) <= time()) {
                    switch ($activity['condition']) {
                        case 0:
                            $item['is_success'] = 1;
                            break;
                        case 1 || 2:
                            if ($item['condition_count'] >= $activity['num']) {
                                $item['is_success'] = 1;
                            }
                            break;
                    }
                    $orderIds = CommunityOrder::find()
                        ->where([
                            'activity_id' => $this->id, 'middleman_id' => $item['user_id'], 'is_delete' => 0,
                            'mall_id' => \Yii::$app->mall->id
                        ])->select('order_id');
                    $count = Order::find()->with('user')
                        ->where([
                            'id' => $orderIds, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'is_pay' => 1,
                            'is_send' => 0
                        ])
                        ->andWhere(['!=', 'cancel_status', 1])
                        ->count();
                    if ($count > 0) {
                        $item['is_refund'] = 0;
                    }
                }
            }
            if ($this->flag == "EXPORT") {
                $exp = new ActivityDetailExport();
                $exp->activity = $activity;
                $exp->fieldsKeyList = explode(',', $this->fields);
                $exp->export($list);
                return false;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'export_list' => (new ActivityDetailExport())->fieldsList(),
                    'pagination' => $pagination ?? '',
                    'activity' => $activity
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getOrderGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = CommunityActivity::findOne($this->id);
            if ($this->middleman_id) {
                /** @var CommunityMiddleman $middleman1 */
                $middleman1 = CommunityMiddleman::find()->with('address')
                    ->where(['user_id' => $this->middleman_id, 'status' => 1])->one();
                $middlemanId = [$middleman1->user_id];
                $middleman = ArrayHelper::filter(ArrayHelper::toArray($middleman1), [
                    'id', 'user_id', 'name', 'mobile'
                ]);
                $middleman['location'] = $middleman1->address->location;
                $middleman['province'] = $middleman1->address->province;
                $middleman['city'] = $middleman1->address->city;
                $middleman['district'] = $middleman1->address->district;
                $middleman['detail'] = $middleman1->address->detail;
            } else {
                $middleman = null;
                $middlemanList = CommunityMiddleman::findAll(['status' => 1]);
                $middlemanId = ArrayHelper::getColumn($middlemanList, 'user_id', false);
                unset($middlemanList);
            }
            $successId = CommunityActivityLocking::find()->where([
                'activity_id' => $activity->id, 'is_delete' => 0
            ])->select('middleman_id')->column();
            // 判断各个团长的活动是否成功
            $res = [];
            $query = CommunityOrder::find()->alias('co')
                ->andWhere(['co.activity_id' => $activity->id, 'co.is_delete' => 0])
                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0]);
            switch ($activity->condition) {
                case 0:
                    unset($query);
                    $successId = array_merge($successId, $middlemanId);
                    break;
                case 1:
                    $res = $query->select('count(distinct co.user_id) as num,middleman_id')
                        ->groupBy('co.middleman_id')->select('count(distinct co.user_id) as num,middleman_id')
                        ->having(['>=', 'num', $activity->num])
                        ->asArray()->all();
                    break;
                case 2:
                    $res = $query->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                        ->andWhere(['od.is_delete' => 0])
                        ->groupBy('co.middleman_id')->select('sum(od.num) num,co.middleman_id')
                        ->having(['>=', 'num', $activity->num])
                        ->asArray()->all();
                    break;
                default:
            }
            foreach ($res as $item) {
                $successId[] = $item['middleman_id'];
            }
            // 只查询活动成功的团长的订单信息
            $list = (new Query())->from(['co' => CommunityOrder::tableName()])
                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                ->where(['co.activity_id' => $this->id, 'co.middleman_id' => $successId])
                ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                ->groupBy('od.goods_id,od.goods_info')
                ->select(["`co`.`id`", "`od`.`goods_id` as `gid`", "sum(`od`.`num`) as `goods_num`", "`od`.`goods_info`"])
                ->orderBy('gid')
//                ->asArray()
                ->all();
            $newList = [];
            if (!empty($list)) {
                foreach ($list as &$item) {
                    $goodsInfo = \Yii::$app->serializer->decode($item['goods_info']);
                    $attr = '';
                    if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                        foreach ($goodsInfo['attr_list'] as $attrItem) {
                            $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                        }
                    }
                    $item['attr_id'] = $goodsInfo['goods_attr']['id'];
                    $item['attr'] = $attr;
                    $item['name'] = $goodsInfo['goods_attr']['name'];
                    $item['no'] = $goodsInfo['goods_attr']['no'];
                    $item['goods_num'] = intval($item['goods_num']);
                    unset($item['goods_info']);

                    $key = $item['gid'] . '_' . $item['attr_id'];
                    if (isset($newList[$key])) {
                        $newList[$key]['goods_num'] += $item['goods_num'];
                    } else {
                        $newList[$key] = $item;
                    }
                }
                unset($item);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => array_values($newList),
                    'activity' => $activity,
                    'middleman' => $middleman
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'data' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}
