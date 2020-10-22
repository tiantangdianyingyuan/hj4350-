<?php

namespace app\plugins\pick\forms\mall;

use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\pick\forms\export\PickStatisticsExport;
use app\plugins\pick\models\PickGoods;

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

        $query->select([
            "gw.`name`,gw.`cover_pic`,
                        SUM(g.`payment_people`) AS `pay_user`,
                        SUM(g.`payment_num`) AS `goods_num`,
                        SUM(g.`payment_amount`) AS `total_pay_price`"
        ]);
        $query->having([
            'and',
            ['>', 'pay_user', 0],
            ['>', 'goods_num', 0],
            ['>', 'total_pay_price', 0],
        ]);
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
        $query = PickGoods::find()->alias('bg')
            ->leftJoin(['g' => Goods::tableName()], 'g.`id` = bg.`goods_id`')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.`goods_warehouse_id`')
            ->where(['bg.mall_id' => \Yii::$app->mall->id])
            ->groupBy('gw.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'bg.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'bg.created_at', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'bg.id desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new PickStatisticsExport();
        $exp->export($query);
    }
}
