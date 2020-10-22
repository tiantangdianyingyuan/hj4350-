<?php


namespace app\plugins\miaosha\forms\api\v2;

use app\forms\mall\export\MiaoshaStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\miaosha\models\MiaoshaGoods;

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


//        $query->select(["CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':00','~',`mg`.`open_time`,':59') AS `miaosha_time`,`gw`.`cover_pic`,`gw`.`name`,
//                                    COUNT(DISTINCT CASE `o`.`is_pay` WHEN 1 THEN `o`.`user_id` ELSE NULL END) AS `user_num`,
//                                    SUM(CASE `o`.`is_pay` WHEN 1 THEN `od`.`num` ELSE 0 END) AS `goods_num`,
//                                    SUM(CASE `o`.`is_pay` WHEN 1 THEN `od`.`total_price` ELSE 0 END) AS `pay_price`,
//CASE WHEN CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':00') > NOW() THEN '未开始' WHEN CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':59') < NOW() THEN '已结束' ELSE '进行中' END AS `status`"]);

        $query->select(["CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':00','~',`mg`.`open_time`,':59') AS `miaosha_time`,`gw`.`cover_pic`,`gw`.`name`,
                                    `g`.`payment_people` AS `user_num`,
                                    `g`.`payment_num` AS `goods_num`,
                                    `g`.`payment_amount` AS `pay_price`,
CASE WHEN CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':00') > NOW() THEN '未开始' WHEN CONCAT(`mg`.`open_date`,' ',`mg`.`open_time`,':59') < NOW() THEN '已结束' ELSE '进行中' END AS `status`"]);

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
        $query = MiaoshaGoods::find()->alias('mg')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.`id`=mg.`goods_warehouse_id`')
            ->leftJoin(['g' => Goods::tableName()], 'g.id = mg.goods_id and g.mall_id = mg.mall_id')
//            ->leftJoin(['od' => OrderDetail::tableName()], 'od.`goods_id`=mg.`goods_id` AND od.`is_delete`=0')
//            ->leftJoin(['o' => Order::tableName()], "o.`id`=od.`order_id` AND o.`sign`= 'miaosha' AND o.`is_delete`=0 AND o.`is_pay`=1 AND o.`is_recycle`=0 AND o.`cancel_status` <> 1 AND o.`mall_id`=" . \Yii::$app->mall->id)
            ->where(['mg.mall_id' => \Yii::$app->mall->id, 'mg.is_delete' => 0])
            ->groupBy('mg.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'mg.open_date', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'mg.open_date', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order . ',mg.`open_date` DESC,mg.`open_time` DESC' : 'mg.`open_date` DESC,mg.`open_time` DESC');

        return $query;
    }


    protected function export($query)
    {
        $exp = new MiaoshaStatisticsExport();
        $exp->export($query);
    }
}