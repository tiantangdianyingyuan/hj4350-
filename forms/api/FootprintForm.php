<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\api;

use app\core\response\ApiCode;
use app\models\FootprintDataLog;
use app\models\FootprintGoodsLog;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\vip_card\models\VipCardDiscount;

class FootprintForm extends Model
{
    public $id;
    public $type;
    public $page;
    public $start_time;
    public $end_time;

    private $list = [];
    private $map = [
        'day' => 'day',
        'pay_price' => 'pay_price',
        'pay_num' => 'pay_num',
        'highest_price' => 'highest_price',
        'coupon_num' => 'coupon_num',
        'coupon_discount_price' => 'coupon_discount_price',
        'member_discount_price' => 'member_discount_price',
        'percentage' => 'percentage'
    ];

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['type', 'start_time', 'end_time'], 'string'],
            [['start_time'], 'default', 'value' => date('Y-m-d 00:00:00', time())]
        ];
    }

    //统计数据
    public function data()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = FootprintDataLog::find()
                ->where(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere(['>', 'statistics_time', date('Y-m-d 00:00:00', time())])
                ->asArray()
                ->all();
            foreach ($list as $value) {
                unset($this->map[$value['key']]);//去除最新记录
                $this->list[$value['key']] = $value['value'];
            }
            //处理剩下的数据
            while (count($this->map) > 0) {
                $rand = array_rand($this->map);
                switch ($rand) {
                    case 'day':
                        $this->day();
                        $this->save(['day']);
                        unset($this->map['day']);
                        break;

                    case 'highest_price':
                        $this->highest_price();
                        $this->save(['highest_price']);
                        unset($this->map['highest_price']);
                        break;
                    case 'percentage':
                        $this->percentage();
                        $this->save(['percentage']);
                        unset($this->map['percentage']);
                        break;
                    case 'pay_price' :
                    case 'pay_num' :
                    case 'member_discount_price' :
                    case 'coupon_discount_price' :
                    case 'coupon_num':
                        $this->order_info();
                        $this->save(['pay_price', 'pay_num', 'member_discount_price', 'coupon_discount_price', 'coupon_num']);
                        unset($this->map['pay_price']);
                        unset($this->map['pay_num']);
                        unset($this->map['member_discount_price']);
                        unset($this->map['coupon_discount_price']);
                        unset($this->map['coupon_num']);
                        break;
                }
            }

            $this->list['type'] = $this->type;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->list,
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
        }
    }

    private function day()
    {
        bcscale(0);
        $user_info = User::findOne(\Yii::$app->user->id);
        $oldday = strtotime(date('Y-m-d', strtotime($user_info->created_at)));
        $today = strtotime(date('Y-m-d', time()));
        $this->list['day'] = (string)bcadd(bcdiv(bcsub($today, $oldday), 86400), 1);
        return;
    }

    private function order_info()
    {
        $model = $this->order_where();
//        $coupon_model = clone $model;
        $order_info = $model
            ->andWhere(['<>', 'o.cancel_status', 1])
            ->andWhere(['od.is_refund' => 0])
            ->groupBy('o.user_id')
            ->asArray()
            ->one();
//        $coupon_order_info = $coupon_model
//            ->groupBy('o.user_id')
//            ->asArray()
//            ->one();
        $this->list['pay_price'] = $order_info['all_total_price'] ?? '0.00';
        $this->list['pay_num'] = $order_info['all_num'] ?? '0';
        $this->list['coupon_discount_price'] = $order_info['all_coupon_discount_price'] ?? '0.00';
        $this->list['member_discount_price'] = $order_info['all_member_discount_price'] ?? '0.00';

        $this->list['coupon_num'] = UserCoupon::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id, 'is_use' => 1])
                ->andWhere(['<', 'created_at', date('Y-m-d 00:00:00', time())])
                ->count() ?? '0';
        return;
    }

    private function highest_price()
    {
        $model = Order::find()->where(['is_delete' => 0, 'is_pay' => 1, 'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id])
            ->orderBy('total_pay_price desc')
            ->asArray()
            ->one();
        $this->list['highest_price'] = $model['total_pay_price'] ?? '0';
        return;
    }

    private function percentage()
    {
        bcscale(2);
        $one_info = Order::find()->select(['sum(total_pay_price) as price'])
            ->where(['is_delete' => 0, 'is_pay' => 1, 'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id])
            ->groupBy('user_id')
            ->asArray()
            ->one();
        if (empty($one_info)) {
            $one_info['price'] = 0;
        }
        //高于这个消费的人数
        $order_count = Order::find()->select(['sum(total_pay_price) as price'])
            ->where(['is_delete' => 0, 'is_pay' => 1, 'mall_id' => \Yii::$app->mall->id])
            ->groupBy('user_id')
            ->having('sum(total_pay_price) > ' . $one_info['price'])
            ->count();
        $user_count = User::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->count();
        //总人数-超过自己的人数-1（不算自己），再除以总人数
        $this->list['percentage'] = bcdiv(bcsub($user_count, $order_count + 1), $user_count) ?? '0';
        return;
    }

    //数据记录
    private function save($list)
    {
        foreach ($list as $item) {
            $model = FootprintDataLog::findOne(['key' => $item, 'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id]);
            if (empty($model)) {
                $model = new FootprintDataLog();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = \Yii::$app->user->id;
                $model->key = $item;
            }
            $model->value = $this->list[$item];
            $model->statistics_time = date('Y-m-d H:i:s', time());
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            unset($model);
        }
        return;
    }

    private function order_where()
    {
        $model = Order::find()->alias('o')
            ->select(['sum(o.coupon_discount_price) as all_coupon_discount_price', 'sum(o.total_pay_price) as all_total_price',
                'sum(od.num) as all_num', 'sum(od.member_discount_price) as all_member_discount_price'])
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
            ->where(['o.is_delete' => 0, 'o.is_pay' => 1, 'o.mall_id' => \Yii::$app->mall->id, 'o.user_id' => \Yii::$app->user->id])
            ->andWhere(['<', 'o.created_at', date('Y-m-d 00:00:00', time())]);
        try {
            \Yii::$app->plugin->getPlugin('vip_card');
            $model->leftJoin(['vcd' => VipCardDiscount::tableName()], 'vcd.order_id = o.id')
                ->select(['sum(o.coupon_discount_price) as all_coupon_discount_price', 'sum(o.total_pay_price) as all_total_price',
                    'sum(od.num) as all_num', '(sum(od.member_discount_price) + sum(vcd.discount)) as all_member_discount_price']);
        } catch (\Exception $exception) {

        }

        return $model;
    }

    //足迹
    public function footprint()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $query = FootprintGoodsLog::find()->alias('fg')
                ->select('g.*,fg.goods_id,fg.updated_at,fg.updated_at as date_time,fg.id')
                ->leftJoin(['g' => Goods::tableName()], 'g.id = fg.goods_id')
                ->andWhere(['fg.mall_id' => \Yii::$app->mall->id, 'fg.user_id' => \Yii::$app->user->id, 'fg.is_delete' => 0,
                    'g.status' => 1]);
            if ($this->start_time) {
                $query->andWhere(['>', 'fg.updated_at', $this->start_time]);
            }
            if ($this->end_time) {
                $query->andWhere(['<', 'fg.updated_at', $this->end_time]);
            }

            $list = $query->with(['attr', 'goodsWarehouse', 'mallGoods'])
                ->page($pagination)
                ->orderBy('fg.updated_at desc')
                ->asArray()
                ->all();
//            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
//            try {
//                $plugin = \Yii::$app->plugin->getPlugin('vip_card');
//            } catch (\Exception $e) {
//                $plugin = false;
//            }
            $group_list = [];
            $group_time = null;
            $key = 0;
            $goods = new Goods();
            foreach ($list as $item) {
//                $item['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice((object)$item);
//                if ($plugin && in_array('vip_card', $permission)) {
//                    $item['vip_card_appoint'] = $plugin->getAppoint($item);
//                }
                $item['attr_groups'] = json_decode($item['attr_groups'], true) ?? $item['attr_groups'];
                $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
                $item['goods_num'] = 0;
                foreach ($item['attr'] as &$value) {
                    $value['attr_list'] = $goods->signToAttr($value['sign_id'], $item['attr_groups']);
                    $item['goods_num'] += $value['stock'];
                }

                $item['page_url'] = '/pages/goods/goods?id=' . $item['goods_id'];
                try {
                    if ($item['sign'] !== '') {
                        $plugin = \Yii::$app->plugin->getPlugin($item['sign']);
                        $item['page_url'] = $plugin->getGoodsUrl(['id' => $item['goods_id'], 'mch_id' => $item['mch_id']]);
                    }
                } catch (\Exception $exception) {
                    \Yii::error($exception);
                }

                $date = date('Y-m-d', strtotime($item['date_time']));
                if (empty($group_time)) {
                    $group_time = $date;
                }
                if ($group_time == $date) {
                    $group_list[$key]['date'] = $group_time;
                    $group_list[$key]['goods'][] = $item;
                } else {
                    $group_time = date('Y-m-d', strtotime($item['date_time']));
                    $key++;
                    $group_list[$key]['date'] = $group_time;
                    $group_list[$key]['goods'][] = $item;
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'list' => $group_list,
                'pagination' => $pagination
            ];

        } catch (\Exception $exception) {
            return ['code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),];
        }
    }

    //足迹删除
    public function footprintDel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->id) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => 'ID不能为空',
            ];
        }
        $model = FootprintGoodsLog::findOne($this->id);
        if (empty($model)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该记录不存在',
            ];
        }
        $model->is_delete = 1;
        if (!$model->save()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $model->errors[0],
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功',
        ];
    }
}
