<?php


namespace app\plugins\booking\forms\api;

use app\forms\mall\export\BookingStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\booking\models\BookingGoods;

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
//                        COUNT(DISTINCT (CASE o.`is_pay` WHEN 1 THEN o.`user_id` ELSE NULL END)) AS `pay_user`,
//                        SUM(CASE o.`is_pay` WHEN 1 THEN `od`.`num` ELSE 0 END) AS `goods_num`,
//                        SUM(CASE o.`is_pay` WHEN 1 THEN `o`.`total_pay_price` ELSE 0 END) AS `total_pay_price`"]);
        $query->select(["gw.`name`,gw.`cover_pic`,
                        g.`payment_people` AS `pay_user`,
                        g.`payment_num` AS `goods_num`,
                        g.`payment_amount` AS `total_pay_price`"]);
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }
        $list = $query
            ->page($pagination)
            ->asArray()
            ->all();

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
        $query = BookingGoods::find()->alias('bg')
            ->leftJoin(['g'=>Goods::tableName()],'g.`id` = bg.`goods_id`')
            ->leftJoin(['gw'=>GoodsWarehouse::tableName()],'gw.id = g.`goods_warehouse_id`')
//            ->leftJoin(['od'=>OrderDetail::tableName()],'od.`goods_id` = bg.`goods_id` AND od.`is_delete` = 0')
//            ->leftJoin(['o'=>Order::tableName()],'o.`id` = od.`order_id` AND o.`is_delete` = 0 AND o.`mall_id` = bg.`mall_id` AND o.`sign` = \'booking\'')
            ->where(['bg.mall_id' => \Yii::$app->mall->id, 'bg.is_delete' => 0])
            ->groupBy('bg.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'bg.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'bg.created_at', $this->date_end. ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'bg.id desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new BookingStatisticsExport();
        $exp->export($query);
    }
}