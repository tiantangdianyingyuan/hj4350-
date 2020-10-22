<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\mall\export\OrderStatisticsExport;
use app\models\Goods;
use app\models\GoodsCats;
use app\models\GoodsCatRelation;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Store;
use app\models\UserInfo;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\mch\models\Mch;

class OrderForm extends Model
{
    public $date_start;
    public $date_end;
    public $user_num;
    public $order_num;
    public $total_pay_price;
    public $goods_num;

    public $mch_id;
    public $store_id;
    public $cats_ids;
    public $cats_name;
    public $sign;

    public $status;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $mch;
    public $store;

    public $platform;

    public $map = [
        'miaosha' => '秒杀订单',
        'pintuan' => '拼团订单',
        'booking' => '预约订单',
        'integral_mall' => '积分商城',
        'bargain' => '砍价订单',
        'advance' => '预售订单',
        'vip_card' => '超级会员卡',
        'pick' => 'N元任选',
    ];

    public function rules()
    {
        return [
            [['user_num', 'order_num', 'goods_num', 'status', 'mch_id', 'store_id'], 'integer'],
            [['flag', 'cats_ids', 'cats_name', 'sign', 'platform'], 'string'],
            [['page', 'limit'], 'integer'],
            [['total_pay_price'], 'double'],
            [['page',], 'default', 'value' => 1],
            [['status',], 'default', 'value' => -1],
            [['date_start', 'date_end', 'fields'], 'trim'],
            [['fields'], 'default', 'value' => ['created_at', 'user_num', 'order_num', 'total_pay_price', 'goods_num']],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        $date_query = $this->date_where();
        $all_query = clone $date_query;
        $now_query = clone $date_query;
        //时间查询
        if ($this->date_start) {
            $all_query->andWhere(['>=', 'o.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $all_query->andWhere(['<=', 'o.created_at', $this->date_end . ' 23:59:59']);
        }
        if ($this->sign == 'advance') {
            $all = $all_query->select("COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`+(`ao`.`deposit`*`ao`.`goods_num`)),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->asArray()
                ->one();

            $now = $now_query->select("COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`+(`ao`.`deposit`*`ao`.`goods_num`)),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->andWhere(['>=', 'o.created_at', date('Y-m-d 00:00:00', time())])
                ->asArray()
                ->one();

            $query->select("DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d') AS `time`,COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`+(`ao`.`deposit`*`ao`.`goods_num`)),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->groupBy('time')
                ->orderBy('time DESC');
        } else {
            $all = $all_query->select("COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->asArray()
                ->one();

            $now = $now_query->select("COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->andWhere(['>=', 'o.created_at', date('Y-m-d 00:00:00', time())])
                ->asArray()
                ->one();

            $query->select("DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d') AS `time`,COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(`o`.`id`) AS `order_num`,COALESCE(SUM(`o`.`total_pay_price`),0) AS `total_pay_price`,COALESCE(sum(`d`.`num`),0) as `goods_num`")
                ->groupBy('time')
                ->orderBy('time DESC');
        }
        //多商户列表
        $list = \Yii::$app->plugin->getList();
        foreach ($list as $value) {
            if ($value['display_name'] == '多商户') {
                $mch_query = $this->mch_where();
                $mch_list = $mch_query->select('m.id,s.name')
                    ->asArray()
                    ->all();
                break;
            } else {
                $mch_list = [];
            }
        }
        if (is_array($mch_list)) {
            foreach ($mch_list as $mch_item) {
                if ($mch_item['id'] == $this->mch_id) {
                    $this->mch = '多商户-' . $mch_item['name'];
                }
            }
        }

        //门店列表
        $store_query = $this->store_where();
        $store_list = $store_query->select('id,name')
            ->asArray()
            ->all();
        if (is_array($store_list)) {
            foreach ($store_list as $store_item) {
                if ($store_item['id'] == $this->store_id) {
                    $this->store = '门店-' . $store_item['name'];
                }
            }
        }
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query->page($pagination)
            ->asArray()
            ->all();


        //插件分类订单list
        $plugins_list = Order::find()->select('sign')
            ->where(['and', ['in', 'sign', ['booking', 'integral_mall', 'miaosha', 'pintuan', 'bargain', 'advance','vip_card', 'pick']], 'mall_id' => \Yii::$app->mall->id,])
            ->groupBy('sign')->asArray()->all();
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        foreach ($plugins_list as $key => $value) {
            $plugins_list[$key]['name'] = $this->map[$value['sign']];
            //预售插件判断是否有权限及是否安装
            if ($value['sign'] == 'advance' && (!in_array($value['sign'], $permission) || !\Yii::$app->plugin->getInstalledPlugin($value['sign']))) {
                unset($plugins_list[$key]);
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'plugins_list' => $plugins_list,
                'mch_list' => $mch_list,
                'store_list' => $store_list,
                'all' => $all,
                'now' => $now,
                'list' => $list,
            ]
        ];
    }

    protected function where()
    {
        $orderQuery = OrderDetail::find()->alias('od')->where(['is_delete' => 0])
            ->select(['od.order_id', 'COALESCE(SUM(`od`.`num`),0) AS `num`'])->groupBy('od.order_id');
        $query = Order::find()->alias('o')->where(['o.is_delete' => 0, 'o.mall_id' => \Yii::$app->mall->id, 'o.is_recycle' => 0])
            ->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])
            ->rightJoin(['d' => $orderQuery], 'd.order_id = o.id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id');

        //订单状态分类查询
        $orderQuery->keyword($this->status == 3, ['od.refund_status' => 1])
            ->keyword($this->status == 4, ['od.refund_status' => 2]);
        $query->keyword($this->status == -1, ['AND', ['not', ['o.cancel_status' => 1]]])
            ->keyword($this->status == 0, ['AND', ['o.is_confirm' => 0], ['not', ['o.cancel_status' => 1]],])
            ->keyword($this->status == 1, ['AND', ['o.is_send' => 1, 'o.is_confirm' => 1], ['not', ['o.cancel_status' => 1]]])
            ->keyword($this->status == 2, ['o.cancel_status' => 1])
            ->keyword($this->status == 3, ['AND', ['not', ['o.cancel_status' => 1]], ['o.sale_status' => 1]])
            ->keyword($this->status == 4, ['AND', ['not', ['o.cancel_status' => 1]], ['o.sale_status' => 1]]);


        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end . ' 23:59:59']);
        }

        //多商户
        if ($this->mch_id) {
            $query->andWhere(['o.mch_id' => $this->mch_id]);
        }
        //门店
        if ($this->store_id) {
            $query->andWhere(['o.store_id' => $this->store_id]);
        }
//        //商品分类
//        if ($this->cats_ids) {
//            $goods_ids = $this->get_goods_ids();
//            $query->andWhere("d.goods_id in ($goods_ids)");
//        }

        //插件分类订单
        if ($this->sign) {
            $query->andWhere(['o.sign' => $this->sign]);
            if ($this->sign == 'advance') {
                $query->leftJoin(['ao' => AdvanceOrder::tableName()], 'ao.order_id = o.id');
            }
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        return $query;
    }

    protected function date_where()
    {
        $orderQuery = OrderDetail::find()->alias('od')->where(['is_delete' => 0])
            ->select(['od.order_id', 'COALESCE(SUM(`od`.`num`),0) AS `num`'])->groupBy('od.order_id');
        $query = Order::find()->alias('o')->where(['o.is_delete' => 0, 'o.mall_id' => \Yii::$app->mall->id, 'o.is_recycle' => 0])
            ->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])
            ->andWhere(['not', ['o.cancel_status' => 1]])
            ->rightJoin(['d' => $orderQuery], 'd.order_id = o.id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id');

        //多商户
        if ($this->mch_id) {
            $query->andWhere(['o.mch_id' => $this->mch_id]);
        }
        //门店
        if ($this->store_id) {
            $query->andWhere(['o.store_id' => $this->store_id]);
        }
//        //商品分类
//        if ($this->cats_ids) {
//            $goods_ids = $this->get_goods_ids();
//            $query->andWhere("d.goods_id in ($goods_ids)");
//        }

        //插件分类订单
        if ($this->sign) {
            $query->andWhere(['o.sign' => $this->sign]);
            if ($this->sign == 'advance') {
                $query->leftJoin(['ao' => AdvanceOrder::tableName()], 'ao.order_id = o.id');
            }
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        return $query;
    }

    protected function mch_where()
    {
        $query = Mch::find()->alias('m')->where(['m.is_delete' => 0, 'm.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['s' => Store::tableName()], 's.mch_id = m.id')
            ->andWhere(['m.review_status' => 1])
            ->orderBy('s.name');

        return $query;
    }

    protected function store_where()
    {
        $query = Store::find()->where(['is_delete' => 0, 'mch_id' => 0, 'mall_id' => \Yii::$app->mall->id,])->orderBy('name');

        return $query;

    }

    public function cats_search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->cats_where();
        $list = $query->select('id,name')
//            ->page($pagination)
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
//                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    protected function cats_where()
    {
        $query = GoodsCats::find()->where(['status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,])->orderBy('sort');

        if ($this->cats_ids) {
            $query->andWhere("parent_id in ($this->cats_ids)");
        } else {
            $query->andWhere(['parent_id' => 0]);
        }

        if ($this->cats_name) {
            $query->andWhere(['like', 'name', $this->cats_name]);
        }

        return $query;
    }

    protected function get_goods_ids()
    {
        $next_cats_ids = $this->cats_ids;
        $up_cats_ids = $next_cats_ids;
        for ($i = 0; $i < 2; $i++) {
            $next_cats_arr = GoodsCats::find()->select('id')->where("parent_id in ($up_cats_ids)")
                ->andWhere(['status' => 1, 'is_delete' => 0])->asArray()->all();
            if (empty($next_cats_arr)) {
                break;
            }
            $up_cats_ids = null;
            foreach ($next_cats_arr as $item) {
                $next_cats_ids .= ',' . $item['id'];
                $up_cats_ids .= (!empty($up_cats_ids) ? ',' : '') . $item['id'];
            }
            $next_cats_arr = null;
        }

        $goods_arr = GoodsCatRelation::find()->alias('cr')->select('g.id')->where(['cr.is_delete' => 0])
            ->andWhere("`cr`.`cat_id` in ($next_cats_ids)")
            ->leftJoin(['g' => Goods::tableName()], 'g.goods_warehouse_id = cr.goods_warehouse_id and g.is_delete = 0')
            ->asArray()
            ->all();

        $goods_ids = null;
        foreach ($goods_arr as $item) {
            $goods_ids .= (!empty($goods_ids) ? ',' : '') . $item['id'];
        }

        return $goods_ids;
    }

    protected function export($query)
    {
        $exp = new OrderStatisticsExport();
        $exp->fieldsKeyList = $this->fields;
        $name = !empty($this->sign) ? $this->map[$this->sign] : (!empty($this->mch) ? $this->mch : $this->store);
        $exp->name = $name;
        $exp->export($query);
    }
}