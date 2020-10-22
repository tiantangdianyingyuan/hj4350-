<?php


namespace app\plugins\step\forms\api;

use app\forms\mall\export\StepExStatisticsExport;
use app\forms\mall\export\StepStatisticsExport;
use app\models\Model;
use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;
use app\models\UserInfo;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityInfo;
use app\plugins\step\models\StepActivityLog;
use app\plugins\step\models\StepDaily;
use app\plugins\step\models\StepGoods;
use app\plugins\step\models\StepOrder;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;

    public $name;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;

    public function rules()
    {
        return [
            [['flag', 'name', 'platform'], 'string'],
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

        $query->select(["sa.`begin_at`,sa.`title`,sa.`step_num`,(sa.`currency` + COALESCE(SUM(sal.`step_currency`),0)) AS `currency`,
                        COUNT(sal.`id`) AS participate_num,
                        SUM(CASE sal.`status` WHEN 2 THEN 1 ELSE 0 END) AS success_num,
                        COALESCE(SUM(sal.`step_currency`),0) AS put_currency"]);

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
        $query = StepActivity::find()->alias('sa')
            ->leftJoin(['sal' => StepActivityLog::tableName()], 'sal.`activity_id` = sa.`id` AND sal.`mall_id` = sa.`mall_id`')
            ->where(['sa.mall_id' => \Yii::$app->mall->id, 'sa.is_delete' => 0])
            ->groupBy('sa.`begin_at`')
            ->orderBy('sa.begin_at desc');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'sa.begin_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'sa.begin_at', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'sa.title', $this->name]);
        }

        return $query;
    }

    public function ex_search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->ex_where();

        $query->select(["  c_date,
                          COALESCE(SUM(sd.`real_num`), 0) AS `step_num`,
                          (COUNT(DISTINCT so.`user_id`)+COUNT(DISTINCT sd.`step_id`)) AS `user_num`,
                          COALESCE(SUM(CASE o.`cancel_status` WHEN 1 THEN 0 ELSE so.`num` END), 0) AS `goods_num`,
                          CONCAT(
                            COALESCE(SUM(CASE o.`cancel_status` WHEN 1 THEN 0 ELSE so.`total_pay_price` END), 0),
                            '/',
                            COALESCE(SUM(CASE o.`cancel_status` WHEN 1 THEN 0 ELSE so.`currency` END), 0)
                          ) AS `goods_pay` "]);

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $new_query->groupBy('c_date')->orderBy('c_date desc');
            $this->ex_export($new_query);
            return false;
        }

        $all_query = clone $query;
        $all_data = $all_query->asArray()->one();
        $list = $query->groupBy('c_date')
            ->orderBy('c_date desc')
            ->page($pagination)
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'all_data' => $all_data,
                'list' => $list,
            ]
        ];
    }

    protected function ex_where()
    {
        $query = StepOrder::find()->from("(SELECT DATE_FORMAT(`created_at`,'%Y-%m-%d') AS `c_date` FROM " . StepDaily::tableName() . " GROUP BY `c_date`
                                                  UNION
                                                  SELECT DATE_FORMAT(`created_at`,'%Y-%m-%d') AS `c_date` FROM " . StepOrder::tableName() . " GROUP BY `c_date`) as a")
            ->leftJoin(['sd' => StepDaily::tableName()], "DATE_FORMAT(sd.`created_at`,'%Y-%m-%d') = a.`c_date` AND sd.`mall_id` = " . \Yii::$app->mall->id)
            ->leftJoin(['so' => StepOrder::tableName()], "DATE_FORMAT(so.`created_at`,'%Y-%m-%d') = a.`c_date` AND so.`is_delete` = 0 AND so.`mall_id` = " . \Yii::$app->mall->id)
            ->leftJoin(['o' => Order::tableName()], "o.`id` = so.`order_id`")
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'c_date', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'c_date', $this->date_end . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        return $query;
    }


    protected function export($query)
    {
        $exp = new StepStatisticsExport();
        $exp->export($query);
    }

    protected function ex_export($query)
    {
        $exp = new StepExStatisticsExport();
        $exp->export($query);
    }
}