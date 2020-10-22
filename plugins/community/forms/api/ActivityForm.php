<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserInfo;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonForm;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityLog;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityOrder;
use app\plugins\community\models\CommunityRelations;
use app\plugins\community\models\CommunitySwitch;
use app\plugins\community\models\Goods;
use yii\db\Exception;
use yii\db\Expression;

/**
 * @property Mall $mall
 */
class ActivityForm extends Model
{
    public $keyword;
    public $id;
    public $start_at;
    public $end_at;
    public $status;

    public $type;

    public $longitude;
    public $latitude;

    public $middleman_id;

    public function rules()
    {
        return [
            [['keyword',], 'trim'],
            [['id', 'status', 'middleman_id'], 'integer'],
            [['start_at', 'end_at'], 'string'],
            [['longitude', 'latitude'], 'number'],
            [['type'], 'integer', 'max' => 2],
            [['type'], 'integer', 'min' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
        ];
    }

    public function getActivityList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = CommunityActivity::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 1
            ])->with('middlemanActivity');

            //用户进入判断
            if ($this->type == 1) {
                //不是通过分享，且未绑定团长，最近的团长
                if (!$this->longitude || !$this->latitude) {
                    throw new Exception('手机位置未授权');
                }
                $relations = CommunityRelations::find()->andWhere(['user_id' => \Yii::$app->user->id, 'is_delete' => 0])->andWhere(['!=', 'middleman_id', 0])->one();
                $middleman_model = CommunityMiddleman::find()->alias('m')->leftJoin(['ca' => CommunityAddress::tableName()], 'ca.user_id = m.user_id')
                    ->where(['m.mall_id' => \Yii::$app->mall->id, 'm.is_delete' => 0, 'm.status' => 1, 'ca.is_delete' => 0, 'ca.is_default' => 1])
                    ->with('userInfo')->select('ca.*');
                if (empty($relations)) {
                    //未绑定，非分享进入
                    if (!$this->middleman_id) {
                        $middleman_info = $middleman_model->asArray()->all();
                        if (empty($middleman_info)) {
                            throw new Exception('附近没有团长');
                        }
                        $distance = 0;
                        $info = [];
                        foreach ($middleman_info as $mitem) {
                            $mitem['distance'] = get_distance($this->longitude, $this->latitude, $mitem['longitude'], $mitem['latitude']);
                            //取最近距离的
                            if ($mitem['distance'] < $distance || $distance == 0) {
                                $distance = $mitem['distance'];
                                $info = $mitem;
                            }
                        }
                        $this->middleman_id = $info['user_id'];
                    }
                } else {
                    $this->middleman_id = $relations->middleman_id;
                }
            } elseif ($this->type == 2) {
                //团长页面
                $this->middleman_id = \Yii::$app->user->id;
            }
            //团长信息判断
            $middleman_info = CommunityMiddleman::find()->where(['user_id' => $this->middleman_id, 'status' => 1, 'is_delete' => 0])->with('address')->one();
            if (empty($middleman_info)) {
                if ($this->type == 1) {
                    throw new Exception('团长信息有误，无法定位活动');
                } elseif ($this->type == 2) {
                    throw new Exception('请先成为社区团长');
                }
            }
            $model->andWhere(['or',
                ['area_limit' => '0,'],
                ['like', 'area_limit', ',' . $middleman_info->address->district_id . ','],
                ['like', 'area_limit', ',' . $middleman_info->address->city_id . ','],
                ['like', 'area_limit', ',' . $middleman_info->address->province_id . ',']]);//前后逗号作为分隔，默认数据0逗号

            if (isset($this->status)) {
                switch ($this->status) {
                    // 全部
                    case -1:
                        $model->orderBy([new Expression("FIELD(`status_name`,'进行中','未开始','已结束')"), 'start_at' => SORT_ASC]);
                        break;
                    // 未开始
                    case 0:
                        $model->andWhere(['>', 'start_at', mysql_timestamp()]);
                        $model->andWhere(['status' => 1]);
                        $model->orderBy(['start_at' => SORT_ASC]);
                        break;
                    // 进行中
                    case 1:
                        $model->andWhere(['<=', 'start_at', mysql_timestamp()]);
                        $model->andWhere(['>=', 'end_at', mysql_timestamp()]);
                        $model->andWhere(['status' => 1]);
                        $model->orderBy(['end_at' => SORT_ASC]);
                        break;
                    // 已结束
                    case 2:
                        $model->andWhere(['<=', 'end_at', mysql_timestamp()]);
//                    $model->andWhere(['status' => 1]);
                        $model->orderBy(['start_at' => SORT_DESC]);
                        break;
                    // 下架中
                    case 3:
                        $model->andWhere(['>', 'end_at', mysql_timestamp()]);
                        $model->andWhere(['status' => 0]);
                        $model->orderBy(['start_at' => SORT_ASC]);
                        break;
                    default:
                        $model->orderBy([new Expression("FIELD(`status_name`,'进行中','未开始','已结束')"), 'start_at' => SORT_ASC]);
                        break;
                }
            }

            $model->select("*,case when start_at < now() and end_at > now() then '进行中' when start_at > now() then '未开始' when end_at < now() then '已结束' end as status_name");
            $list = $model->page($pagination)->asArray()->all();

            foreach ($list as $key => &$item) {
                $item['condition_count'] = 0;
                $virtual_sales = (CommunityGoods::find()->alias('cg')->where(['cg.activity_id' => $item['id'], 'cg.is_delete' => 0])
                        ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1])
                        ->keyword(
                            $this->type == 1,
                            ['not in', 'cg.goods_id', CommunitySwitch::find()->select('goods_id')
                                ->where(['activity_id' => $item['id'], 'middleman_id' => $this->middleman_id, 'is_delete' => 0])]
                        )
                        ->select('sum(g.virtual_sales) as virtual_sales')->asArray()->one())['virtual_sales'] ?? 0;
                switch ($item['condition']) {
                    case 1:
                        $item['condition_count'] = CommunityOrder::find()->alias('co')
                                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                                ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                                ->andWhere(['co.middleman_id' => $this->middleman_id, 'co.activity_id' => $item['id'], 'co.is_delete' => 0])
                                ->groupBy('co.user_id')->count() ?? 0;
                        $robot_count = bcmul($virtual_sales, 0.95, 0);//虚拟销量95%转换成人数
                        if ($this->type == 1) {
                            $item['condition_count'] += $robot_count;
                            $item['num'] += $robot_count;
                        }
                        break;
                    case 2:
                        $item['condition_count'] = CommunityOrder::find()->alias('co')
                                ->andWhere(['co.middleman_id' => $this->middleman_id, 'co.activity_id' => $item['id'], 'co.is_delete' => 0])
                                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                                ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                                ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                                ->andWhere(['od.is_delete' => 0])
                                ->sum('od.num') ?? 0;
                        if ($this->type == 1) {
                            $item['condition_count'] += $virtual_sales;
                            $item['num'] += $virtual_sales;
                        }
                        break;
                    default:
                        $item['condition_count'] = 0;
                }

                $item['is_success'] = 0;
                //锁定成团
                if (!empty(CommunityActivityLocking::findOne(['middleman_id' => $this->middleman_id, 'activity_id' => $item['id'], 'is_delete' => 0]))) {
                    $item['is_success'] = 1;
                }
                if (strtotime($item['start_at']) <= time()) {
                    switch ($item['condition']) {
                        case 0:
                            $item['is_success'] = 1;
                            break;
                        case 1 || 2:
                            if ($item['condition_count'] >= $item['num']) {
                                $item['is_success'] = 1;
                            }
                            break;
                    }
                }


                $item['activity_status'] = CommonForm::timeSlot($item);
                $item['communityGoods_4'] = [];
                $goods_model = CommunityGoods::find()->alias('cg')->where(['cg.activity_id' => $item['id'], 'cg.is_delete' => 0])
                    ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1]);
                if ($this->type == 1) {
                    $goods_model->andWhere(['not in', 'cg.goods_id',
                        CommunitySwitch::find()->where(['middleman_id' => $this->middleman_id, 'activity_id' => $item['id'], 'is_delete' => 0])->select('goods_id')]);
                }

                $item['goods_count'] = $goods_model->count();
                if ($item['goods_count'] > 0) {
                    $goods = $goods_model->orderBy('g.sort')->limit(4)->with('goods')->all();
                    foreach ($goods as $k => $value) {
                        $item['communityGoods_4'][$k]['cover_pic'] = $value->goods->getCoverPic();
                    }
                }

                $item['is_remind'] = $item['middlemanActivity']['is_remind'] ?? 0;

                //商品数量0，隐藏该活动
//                if ($item['goods_count'] == 0) {
//                    unset($list[$key]);
//                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'list' => array_merge($list),
                'pagination' => $pagination
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


    public function getActivityDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id) {
                throw new Exception('活动ID不能为空');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => CommonActivity::getActivityDetail($this->id, \Yii::$app->user->id, 2)//团长标识
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    //活动数据
    public function getLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id) {
                throw new Exception('活动ID不能为空');
            }
            $order_query = CommunityOrder::find()->alias('co')->where(['co.activity_id' => $this->id, 'co.middleman_id' => \Yii::$app->user->id, 'co.is_delete' => 0]);
            $log_query = CommunityLog::find()->alias('cl')->andWhere(['cl.activity_id' => $this->id, 'cl.middleman_id' => \Yii::$app->user->id, 'cl.is_delete' => 0])->groupBy('cl.user_id');
            $data = [
                'order_num' => $order_query->count(),
                'user_num' => $order_query->groupBy('co.user_id')->count(),
                'order_price' => $order_query->leftJoin(['o' => Order::tableName()], 'co.order_id = o.id')
                        ->leftJoin(['or'=>OrderRefund::tableName()],'or.order_id = o.id and or.status = 2 and or.is_confirm = 1')
                        ->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])->andWhere(['!=', 'o.cancel_status', 1])
                        ->select('(o.total_pay_price - or.refund_price) as total_pay_price')->sum('total_pay_price') ?? 0,
                'log_num' => $log_query->count(),
                'list' => $log_query->leftJoin(['u' => User::tableName()], 'u.id = cl.user_id')->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
                    ->select(['i.avatar', 'u.nickname', 'cl.created_at'])->orderBy('cl.created_at desc')->page($pagination)->asArray()->all()
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data
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
}
