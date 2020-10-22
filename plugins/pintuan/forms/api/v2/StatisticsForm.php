<?php


namespace app\plugins\pintuan\forms\api\v2;

use app\forms\mall\export\PintuanStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;
    public $order;

    public $name;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['flag', 'name', 'order'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['date_start', 'date_end', 'fields'], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

//        $query->select(["gw.`name`,gw.`cover_pic`,
//                        COUNT(DISTINCT por.`user_id`) AS `all_user`,
//                        COUNT(DISTINCT (CASE por.`is_groups` WHEN 1 THEN por.`user_id` ELSE NULL END)) AS `success_user`,
//                        COUNT(DISTINCT (CASE o.`is_pay` WHEN 1 THEN o.`user_id` ELSE NULL END)) AS `pay_user`,
//                        SUM(CASE o.`is_pay` WHEN 1 THEN `od`.`num` ELSE 0 END) AS `goods_num`,
//                        SUM(CASE o.`is_pay` WHEN 1 THEN `o`.`total_pay_price` ELSE 0 END) AS `total_pay_price`,
//                        CASE WHEN pg.`end_time`>=DATE_FORMAT(NOW(),'%Y-%m-%d') THEN '进行中' ELSE '已结束' END AS `status`"]);

        $query->select(["gw.`name`,gw.`cover_pic`,
                        COUNT(DISTINCT por.`user_id`) AS `all_user`,
                        SUM(DISTINCT (CASE po.`status` WHEN 2 THEN po.`people_num` ELSE 0 END)) AS `success_user`,
                        COALESCE(SUM(DISTINCT (CASE po.`status` WHEN 2 THEN po.`people_num` ELSE 0 END))/COUNT(DISTINCT por.`user_id`),0) as `success_100`,
                        g.`payment_people` AS `pay_user`,
                        g.`payment_num` AS `goods_num`,
                        g.`payment_amount` AS `total_pay_price`,
                        CASE WHEN pg.`end_time`>=DATE_FORMAT(NOW(),'%Y-%m-%d') THEN '进行中' ELSE '已结束' END AS `status`"]);

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query
            ->page($pagination)
            ->asArray()
            ->all();
        foreach ($list as $key => $item) {
            $list[$key]['success_100'] = (($item['all_user'] == 0) ? 0 : floor($item['success_100'] * 100)) . '%';
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    protected function where()
    {
        $query = PintuanGoods::find()->alias('pg')
            ->leftJoin(['g' => Goods::tableName()], 'g.`id` = pg.`goods_id`')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.`id` = g.`goods_warehouse_id`')
//            ->leftJoin(['od' => OrderDetail::tableName()], 'od.`goods_id` = pg.`goods_id`  AND od.`is_delete` = 0')
//            ->leftJoin(['o' => Order::tableName()], 'o.`id` = od.`order_id` AND o.`mall_id` = pg.`mall_id` AND o.`cancel_status` <> 1  AND o.`is_delete` = 0 AND o.`sign` = \'pintuan\'')
            ->leftJoin(['po' => PintuanOrders::tableName()], 'po.`goods_id` = pg.`goods_id` and po.`mall_id` = pg.`mall_id`')
            ->leftJoin(['por' => PintuanOrderRelation::tableName()], 'por.`pintuan_order_id` = po.`id` AND por.`is_delete` = 0')
            ->where(['pg.mall_id' => \Yii::$app->mall->id, 'pg.is_delete' => 0])
            ->andWhere(['g.is_delete' => 0, 'gw.is_delete' => 0, 'g.mall_id' => \Yii::$app->mall->id])
            ->groupBy('pg.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'pg.end_time', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'pg.end_time', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'pg.id desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new PintuanStatisticsExport();
        $exp->export($query);
    }
}