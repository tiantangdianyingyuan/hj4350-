<?php


namespace app\plugins\stock\forms\mall;

use app\plugins\stock\forms\export\StockStatisticsExport;
use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\stock\models\StockCashLog;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;
    public $order_by;

    public $page;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['flag', 'order_by'], 'string'],
            [['page'], 'integer'],
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

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $q1 = StockCashLog::find()->alias('a')->leftJoin(['b' => StockOrder::tableName()],
                'b.bonus_id = a.bonus_id and b.is_delete = 0 and b.mall_id = ' . \Yii::$app->mall->id)
                ->where(['a.is_delete' => 0, 'a.type' => 1, 'a.mall_id' => \Yii::$app->mall->id])
                ->andWhere(['a.level_id' => $item['id']]);
            if ($this->date_start) {
                $q1->andWhere(['>=', 'a.created_at', $this->date_start . ' 23:59:59']);
            }
            if ($this->date_end) {
                $q1->andWhere(['<=', 'a.created_at', $this->date_end . ' 23:59:59']);
            }

            $q2 = clone $q1;
            $q3 = clone $q1;
            $q4 = clone $q1;

            $order_num = $q2->select('COUNT(DISTINCT(`b`.`order_id`)) as order_num')
                ->asArray()
                ->all();

            $item['order_num'] = $order_num['order_num'] ?? 0;

            $bonus_price = $q3->select('SUM(`a`.`price`) as bonus_price')
                ->groupBy('b.id')
                ->asArray()
                ->one();
            $item['bonus_price'] = $bonus_price['bonus_price'] ?? 0;

            $user_num = $q4->select('COUNT(DISTINCT(`a`.`user_id`)) as user_num')
                ->asArray()
                ->one();
            $item['user_num'] = (int)$user_num['user_num'] ?? 0;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    protected function where()
    {
        $query = StockLevel::find()
            ->where(['mall_id' => \Yii::$app->mall->id]);
        if ($this->order_by) {
            $query->orderBy($this->order_by);
        } else {
            $query->orderBy('is_default desc,bonus_rate');
        }
        return $query;
    }


    protected function export($query)
    {
        $exp = new StockStatisticsExport();
        $exp->export($query);
    }
}
