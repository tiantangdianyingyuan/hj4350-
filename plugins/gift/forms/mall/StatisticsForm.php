<?php


namespace app\plugins\gift\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\export\GiftStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;

class StatisticsForm extends Model
{
    public $name;
    public $order;

    public $date_start;
    public $date_end;

    public $page;
    public $limit;

    public $flag;

    public function rules()
    {
        return [
            [['flag'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['name', 'order'], 'string'],
            [['date_start', 'date_end'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();
        $query->select([
            'gw.name', 'gw.cover_pic', 'od.goods_info', 'od.goods_id', 'od.goods_attr_id', 'o.gift_id',
            'sum(od.num) as goods_num', 'sum(od.total_price) as total_price',
            'count(o.user_id) as user_num'
        ])->groupBy('od.goods_id,od.goods_attr_id');

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
            $goods_info = json_decode($item['goods_info'], true);
            $item['attr'] = '';
            foreach ($goods_info['attr_list'] as $attr) {
                $item['attr'] .= $attr['attr_group_name'] . ':' . $attr['attr_name'] . ' ';
            }
            $item['convert_num'] = GiftOrder::find()
                    ->andWhere(['goods_id' => $item['goods_id'], 'goods_attr_id' => $item['goods_attr_id'], 'is_refund' => 0])
                    ->sum('num') ?? '0';
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
        $query = GiftSendOrderDetail::find()->alias('od')
            ->leftJoin(['o' => GiftSendOrder::tableName()], 'o.id = od.send_order_id')
            ->leftJoin(['g' => Goods::tableName()], 'g.id = od.goods_id')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
            ->andWhere(['o.mall_id' => \Yii::$app->mall->id, 'o.is_pay' => 1]);

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'od.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'od.created_at', $this->date_end . ' 23:59:59']);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'od.`created_at` desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new GiftStatisticsExport();
        $exp->export($query);
    }
}