<?php

/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityActivityRobots;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use app\plugins\community\models\CommunityOrder;
use app\plugins\community\models\CommunityRobots;
use app\plugins\community\models\CommunitySwitch;
use app\plugins\community\models\Goods;
use app\plugins\community\Plugin;
use yii\db\Exception;
use yii\db\Query;
use app\helpers\ArrayHelper;

class CommonActivity extends Model
{
    public static function getActivityDetail($activity_id, $middleman_id, $type = 1)
    {

        if (!$activity_id) {
            throw new Exception('活动ID不能为空');
        }

        if (!$middleman_id) {
            throw new Exception('团长ID不能为空');
        }

        $address = CommunityAddress::findOne(['user_id' => $middleman_id, 'is_default' => 1, 'is_delete' => 0]);
        if (empty($address)) {
            throw new Exception('团长自提点不正确');
        }
        $data = CommunityActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $activity_id,
            'is_delete' => 0
        ])->andWhere(['or',
            ['area_limit' => '0,'],
            ['like', 'area_limit', ',' . $address->district_id . ','],
            ['like', 'area_limit', ',' . $address->city_id . ','],
            ['like', 'area_limit', ',' . $address->province_id . ',']])//前后逗号作为分隔，默认数据0逗号
        ->with('middlemanActivity')->asArray()->one();

        if (!$data) {
            throw new \Exception('周边没有活动可参加');
        }

        $data['condition'] = intval($data['condition']);

        $data['address'] = $address;
        $data['full_price'] = json_decode($data['full_price'], true);

        $data['is_remind'] = $data['middlemanActivity']['is_remind'] ?? 0;
        unset($data['middlemanActivity']);

        $data['condition_count'] = 0;

        switch ($data['condition']) {
            case 1:
                $data['condition_count'] = CommunityOrder::find()->alias('co')
                        ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                        ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                        ->andWhere(['co.middleman_id' => $middleman_id, 'co.activity_id' => $data['id'], 'co.is_delete' => 0])
                        ->groupBy('co.user_id')->count() ?? 0;
                break;
            case 2:
                $data['condition_count'] = CommunityOrder::find()->alias('co')
                        ->andWhere(['co.middleman_id' => $middleman_id, 'co.activity_id' => $data['id'], 'co.is_delete' => 0])
                        ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                        ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
                        ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
                        ->andWhere(['od.is_delete' => 0])
                        ->sum('od.num') ?? 0;
                break;
            default:
                $data['condition_count'] = 0;
        }

        $data['is_success'] = 0;
        //锁定成团
        if (!empty(CommunityActivityLocking::findOne(['middleman_id' => $middleman_id, 'activity_id' => $data['id'], 'is_delete' => 0]))) {
            $data['is_success'] = 1;
        }
        if (strtotime($data['start_at']) <= time()) {
            //条件成团
            switch ($data['condition']) {
                case 0:
                    $data['is_success'] = 1;
                    break;
                case 1 || 2:
                    if ($data['condition_count'] >= $data['num']) {
                        $data['is_success'] = 1;
                    }
                    break;
            }
        }

        $data['time'] = strtotime($data['start_at']) - time();//距离开始时间倒计时

        $setting = CommonSetting::getCommon()->getSetting();

        $data['activity_status'] = CommonForm::timeSlot($data);

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\community\models\Goods';
        $form->sign = 'community';
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->status = 1;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;

        if ($setting['sell_out_sort'] == 2) {
            $query->andWhere(['>', 'g.goods_stock', 0]);
            $query->orderBy('g.sort ASC');
        } elseif ($setting['sell_out_sort'] == 3) {
            $query->orderBy('`g`.`goods_stock` = 0,`g`.`sort`');
        } else {
            $query->orderBy('g.sort ASC');
        }

        $query->rightJoin(['cg' => CommunityGoods::tableName()], 'cg.goods_id = g.id and cg.is_delete = 0 and cg.activity_id = ' . $activity_id)
            ->addSelect('cg.activity_id');

        if ($type == 1) {
            $query->andWhere(
                ['not in', 'cg.goods_id', CommunitySwitch::find()->select('goods_id')
                    ->where(['activity_id' => $activity_id, 'middleman_id' => $middleman_id, 'is_delete' => 0])]
            );
        }

        $list = $query->page($pagination)->asArray()->all();

        $all_virtual_sales = 0;
        $data['all_stock'] = 0;
        $goods = new Goods();
        foreach ($list as &$item) {
            $price = [];
            $profit_price = [];
            $item['attr_groups'] = \Yii::$app->serializer->decode($item['attr_groups']);
            $attrList = $goods->resetAttr($item['attr_groups']);
            foreach ($item['attr'] as &$a) {
                $a['attr_list'] = $attrList[$a['sign_id']];
                array_push($price, (float)$a['price']);
                $attr = CommunityGoodsAttr::findOne(['goods_id' => $item['id'], 'attr_id' => $a['id']]);
                if (empty($attr)) {
                    continue;
                }
                array_push($profit_price, bcsub($a['price'], $attr->supply_price));
                $a['stock'] = intval($a['stock']);
            }
            unset($a);
            if (empty($profit_price)) {
                $profit_price = [0];
            }

            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['type'] = $item['goodsWarehouse']['type'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];

            $all_virtual_sales += floatval($item['virtual_sales']);

            $item['sales'] = '已售：' . ($item['sales'] + ($type == 1 ? $item['virtual_sales'] : 0)) . '件';
            $item['page_url'] = '/plugins/community/detail/detail?goods_id=' . $item['id'];
            $item['is_open'] = empty(CommunitySwitch::findOne(['activity_id' => $activity_id, 'goods_id' => $item['id'], 'middleman_id' => $middleman_id, 'is_delete' => 0])) ? 1 : 0;
            $item['min_price'] = min($price) ?? 0;
            switch ($item['use_attr']) {
                case 0:
                    $item['profit_price'] = price_format(max($profit_price) ?? 0);
                    break;
                case 1:
                    $item['profit_price'] = [
                        'min_price' => price_format(min($profit_price) ?? 0),
                        'max_price' => price_format(max($profit_price) ?? 0),
                    ];
                    break;
            }

            $data['all_stock'] += ($item['goods_stock'] + $item['virtual_sales']);//库存加上虚拟销量，防止销量大于库存

            //商品开关，0关，1开
            if ($type == 2) {
                $item['switch'] = CommunitySwitch::getSwitch($item['id'], $middleman_id) ? 0 : 1;
            }
            $item['goodsWarehouse'] = [
                'unit' => $item['goodsWarehouse']['unit']
            ];
            $item['goods_stock'] = intval($item['goods_stock']);
            $item['use_attr'] = intval($item['use_attr']);
            $item = ArrayHelper::filter($item, [
                'activity_id', 'attr', 'attr_groups', 'cover_pic', 'goodsWarehouse', 'goods_stock', 'id', 'min_price',
                'name', 'page_url', 'price', 'profit_price', 'sales', 'virtual_sales', 'use_attr', 'type', 'sign',
                'switch'
            ]);
        }
        unset($item);
        $data['all_sales'] = intval(CommunityOrder::find()->alias('co')
                ->where(['co.activity_id' => $activity_id, 'co.middleman_id' => $middleman_id, 'co.is_delete' => 0])
                ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                ->andWhere(['and', ['o.is_delete' => 0, 'is_recycle' => 0], ['!=', 'o.cancel_status', 1]])
                ->sum('co.num')) ?? 0;
        if ($type == 1) {
            $data['all_sales'] += $all_virtual_sales;
        }
//        $data['rate'] = $data['all_stock'] == 0 ? 100 : bcdiv($data['all_sales'], $data['all_stock'], 4) * 100;//比例

        //参与的用户，多退少补，根据销量补机器人，只查询7个
        $user_model = CommunityOrder::find()->alias('co')
            ->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
            ->andWhere(['!=', 'o.cancel_status', 1])->andWhere(['o.is_delete' => 0, 'o.is_recycle' => 0])
            ->andWhere(['co.middleman_id' => $middleman_id, 'co.activity_id' => $data['id'], 'co.is_delete' => 0]);
        $count = $user_model->count();
        $user_list = $user_model->limit(7)->with('user.userInfo')->orderBy('co.created_at desc')->all();

        $user_arr = [];
        if ($count != 0) {
            foreach ($user_list as $k => $value) {
//                $user_arr[$k]['nickname'] = $value['user']['nickname'];
                $user_arr[$k]['avatar'] = $value['user']['userInfo']['avatar'];
                $user_arr[$k]['time'] = time() - strtotime($value['created_at']);
            }
        }
        $robot_count = ceil(bcmul($all_virtual_sales, 0.95, 2));//虚拟销量95%转换成人数
        $data['user_num'] = $count;
        if ($type == 1) {
            $data['user_num'] += $robot_count;
        }
        //补充机器人
        $robots_info = CommunityActivityRobots::findOne(['activity_id' => $activity_id, 'middleman_id' => $middleman_id, 'is_delete' => 0]);
        if (empty($robots_info)) {
            $ids_arr = [];
            //随机7个机器人ID
            while (true) {
                $num = mt_rand(1, 30);
                if (!in_array($num, $ids_arr)) {
                    $ids_arr[] = $num;
                }
                if (count($ids_arr) >= 7) {
                    break;
                }
            }
            //保存对应活动团长，保证机器人头像不变动
            $robots_info = new CommunityActivityRobots();
            $robots_info->activity_id = $activity_id;
            $robots_info->middleman_id = $middleman_id;
            $robots_info->robots_ids = implode(',', $ids_arr);
            $robots_info->save();
        }
        if ($robot_count > 0) {
            $robots_ids_arr = explode(',', $robots_info->robots_ids);
            $host_url = \Yii::$app->request->getHostInfo();
            for ($i = 0; $i < (($robot_count <= 7 ? $robot_count : 7) - $count); $i++) {
                //根据ID去机器人头像，默认活动开始就参加活动
                array_push($user_arr, ['avatar' => $host_url . '/web/statics/img/plugins/community/user_avatar/user_' . $robots_ids_arr[$i] . '.png', 'time' => time() - strtotime($data['start_at'])]);
            }
        }

        //条件加虚拟数量
        if ($type == 1) {
            if ($data['condition'] == 1) {
                $data['condition_count'] += $robot_count;
                $data['num'] += $robot_count;
            }
            if ($data['condition'] == 2) {
                $data['condition_count'] += $all_virtual_sales;
                $data['num'] += $all_virtual_sales;
            }
        }
        $data['rate'] = $data['num'] == 0 ? 0 : bcdiv($data['condition_count'], $data['num'], 4) * 100;//比例
        $data['rate'] = $data['rate'] <= 100 ? $data['rate'] : 100;
        //推荐活动
        $model = CommunityActivity::find()->alias('ca')->where([
            'ca.mall_id' => \Yii::$app->mall->id,
            'ca.is_delete' => 0
        ]);
        $address = CommunityAddress::findOne(['user_id' => $middleman_id, 'is_default' => 1, 'is_delete' => 0]);
        $model->andWhere(['or', ['ca.area_limit' => '0,'], ['like', 'ca.area_limit', ',' . $address->district_id . ','],
            ['like', 'ca.area_limit', ',' . $address->city_id . ','], ['like', 'ca.area_limit', ',' . $address->province_id . ',']])
            ->andWhere(['<=', 'ca.start_at', mysql_timestamp()])
            ->andWhere(['>=', 'ca.end_at', mysql_timestamp()])
            ->andWhere(['ca.status' => 1])
            ->leftJoin(['cg' => CommunityGoods::tableName()], 'cg.activity_id = ca.id and cg.is_delete = 0')
            ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1]);

        $recommend_info = $model->orderBy('start_at desc')->asArray()->one();

        $goods_list = [];
        if (!empty($recommend_info)) {
            $goods = CommunityGoods::find()->alias('cg')->where(['cg.activity_id' => $recommend_info['id'], 'cg.is_delete' => 0])
                ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1])
                ->orderBy('g.sort')->limit(4)->with('goods')->all();

            foreach ($goods as $k => $goods_item) {
                $goods_list[$k]['cover_pic'] = $goods_item->goods->getCoverPic();
                if ($k >= 2) {
                    break;
                }
            }
        }

        return [
            'activity' => ArrayHelper::filter($data, [
                'id', 'activity_status', 'all_sales', 'all_stock', 'condition', 'condition_count', 'full_price', 'num',
                'rate', 'start_at', 'end_at', 'time', 'title', 'user_num', 'is_success'
            ]),
            'user_list' => array_reverse($user_arr),//倒序输出
            'list' => $list,
            'pagination' => $pagination,
            'recommend_activity' => [
                'activity_id' => $recommend_info['id'] ?? 0,
                'count' => count($goods_list) ?? 0,
                'goods_list' => $goods_list
            ],
            'last_mobile' => Order::find()->andWhere(['user_id' => \Yii::$app->user->id, 'sign' => (new Plugin())->getName()])
                    ->limit(1)->orderBy('created_at desc')->select('mobile')->column()[0] ?? '',
            'template_message_list' => self::getTemplateMessage(),
        ];
    }

    /* @var CommunityActivity[] $activityList */
    public static $activityList;

    public static function getActivity($id)
    {
        if (isset(self::$activityList[$id])) {
            return self::$activityList[$id];
        }
        $activity = CommunityActivity::find()->with(['communityGoods.goods.attr', 'communityGoods.goods.goodsWarehouse'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $id])
            ->one();
        self::$activityList[$id] = $activity;
        return self::$activityList[$id];
    }

    protected static function getTemplateMessage()
    {
        $arr = ['order_pay_tpl', 'order_cancel_tpl', 'pick_up_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }
}
