<?php


namespace app\plugins\integral_mall\forms\api;


use app\core\response\ApiCode;
use app\forms\mall\export\IntegralMallStatisticsExport;
use app\models\IntegralLog;
use app\models\Model;
use app\models\Order;
use app\models\UserInfo;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallOrders;
use yii\db\Expression;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;


    public function rules()
    {
        return [
            [['flag', 'platform'], 'string'],
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

        $query->select("  DATE_FORMAT(a.`created_at`, '%Y-%m-%d') AS `date`,
                                      COUNT(a.`id`) AS `coupons_num`,
                                      COUNT(DISTINCT d.`user_id`) AS `user_num`,
                                      COUNT(d.`id`) AS `goods_num`,
                                      COALESCE(SUM(a.`integral_num`),0) AS `goods_integral`,
                                      COALESCE(SUM(d.`total_goods_price`),0) AS `goods_price`");
//        $query->select("  DATE_FORMAT(a.`created_at`, '%Y-%m-%d') AS `date`,
//                                      COUNT(b.`id`) AS `coupons_num`,
//                                      COUNT(DISTINCT d.`user_id`) AS `user_num`,
//                                      COUNT(d.`id`) AS `goods_num`,
//                                      COALESCE(SUM(c.`integral_num`),0) AS `goods_integral`,
//                                      COALESCE(SUM(d.`total_goods_price`),0) AS `goods_price`");

        $all_query = clone $query;
        $all_data = $all_query->asArray()->all();
//        $now_query=clone $query;
//        $now_data=$now_query->andWhere(['like','a.created_at',date('Y-m-d')])->asArray()->all();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $new_query->groupBy('`date`')->orderBy('`date` desc');
            $this->export($new_query);
            return false;
        }

        $list = $query->groupBy('`date`')->orderBy('`date` desc')
            ->page($pagination)
            ->asArray()
            ->all();

        unset($all_data[0]['date']);
//        unset($now_data[0]['date']);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'all_data' => $all_data,
//                'now_list'=>$now_data,
                'list' => $list,
            ]
        ];
    }

    protected function where()
    {
        //`is_delete` AS `order_id`曲线救国，YII框架问题，不识别第一个后面的NULL或空字符,只能取固定数等于0的字段拯救
        $query = IntegralLog::find()
            ->from(
                "(SELECT `id`,`is_delete` AS `integral_num`,`is_delete` AS `order_id`,`created_at`,`user_id` FROM " . IntegralMallCouponsOrders::tableName()
                . " WHERE `is_delete` = 0 and `mall_id` = " . \Yii::$app->mall->id
                . " UNION ALL SELECT " . new Expression('NULL as id') . ",`integral_num`,`order_id`,`created_at`,`is_delete` AS `user_id` FROM "
                . IntegralMallOrders::tableName() . " WHERE `is_delete` = 0 and `mall_id` = " . \Yii::$app->mall->id . ") AS a "
            )
            ->leftJoin(['d' => Order::tableName()], 'd.id = a.order_id and d.cancel_status <> 1')
            ->leftJoin(['e' => UserInfo::tableName()], 'e.user_id = d.user_id or e.user_id = a.user_id');
//        $query = IntegralLog::find()
//            ->from("(SELECT created_at FROM " . IntegralMallCouponsOrders::tableName() . " WHERE is_delete = 0 and mall_id = " . \Yii::$app->mall->id . " UNION
//            SELECT created_at FROM " . IntegralMallOrders::tableName() . " WHERE is_delete = 0 and mall_id = " . \Yii::$app->mall->id . ") AS a ")
//            ->leftJoin(['b' => IntegralMallCouponsOrders::tableName()], 'b.created_at = a.created_at')
//            ->leftJoin(['c' => IntegralMallOrders::tableName()], 'c.created_at = a.created_at')
//            ->leftJoin(['d' => Order::tableName()], 'd.id = c.order_id and d.cancel_status <> 1')
//            ->leftJoin(['e' => UserInfo::tableName()], 'e.user_id = d.user_id or e.user_id = b.user_id');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'a.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'a.created_at', $this->date_end . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['e.platform' => $this->platform]);
        }
        return $query;
    }


    protected function export($query)
    {
        $exp = new IntegralMallStatisticsExport();
        $exp->export($query);
    }
}