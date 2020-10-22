<?php


namespace app\plugins\community\forms\api;

use app\forms\mall\export\CommunityStatisticsExport;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\models\OrderDetail;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\Goods;
use app\plugins\community\models\Order;

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
            'cg.goods_id',
            'count(DISTINCT o.user_id) as user_num',
            'coalesce(sum(o.is_pay),0) as pay_user_num',
            'sum(case o.is_pay when 1 then od.num else 0 end) as pay_goods_num',
            'sum(case o.is_pay when 1 then o.total_pay_price else 0 end) as pay_price',
            "case when `ca`.`start_at` > '" . mysql_timestamp() . "' then '未开始' when `ca`.`end_at` < '" . mysql_timestamp() . "' then '已结束' else '进行中' end as `activity_status`"
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
        foreach ($list as &$item) {
            $goods = Goods::findOne($item['goods_id']);
            $item['name'] = $goods->getName();
            $item['cover_pic'] = $goods->getCoverPic();
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
        $query = CommunityGoods::find()->alias('cg')->where(['cg.mall_id' => \Yii::$app->mall->id])
            ->leftJoin(['ca' => CommunityActivity::tableName()], 'ca.id = cg.activity_id')
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.goods_id = cg.goods_id')
            ->leftJoin(['o' => Order::tableName()], 'o.id = od.order_id')
            ->groupBy('cg.goods_id');


        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'ca.start_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'ca.end_at', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['cg.goods_id' => Goods::find()->alias('g')
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')->where(['like', 'gw.name', $this->name])->select('g.id')]);
        }

        $query->orderBy(!empty($this->order) ? $this->order . ',cg.id' : "ca.end_at");

        return $query;
    }


    protected function export($query)
    {
        $exp = new CommunityStatisticsExport();
        $exp->export($query);
    }
}