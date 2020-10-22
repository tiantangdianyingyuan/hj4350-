<?php


namespace app\plugins\bargain\forms\api;

use app\forms\mall\export\BargainStatisticsExport;
use app\forms\mall\export\LotteryStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\Code;

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


        $query->select(["gw.`name`,gw.`cover_pic`,g.`attr_groups`,bg.`min_price`,bg.`initiator`,bg.`participant`,bg.`min_price_goods`,bg.`underway`,bg.`success`,bg.`fail`,g.`payment_people`,g.`payment_num`,g.`payment_amount`,
                        CASE WHEN bg.`begin_time` > NOW() THEN '未开始' WHEN bg.`end_time` < NOW() THEN '已结束' ELSE '进行中' END AS `status`"]);
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query
            ->page($pagination)
            ->asArray()
            ->all();
        foreach ($list as $key => $v) {
            $attr = json_decode($v['attr_groups'], true);
            if (is_array($attr)) {
                $attr_groups = '';
                foreach ($attr as $item) {
                    $attr_groups .= $item['attr_group_name'] . ':' . $item['attr_list'][0]['attr_name'] . ' ';
                }
                $list[$key]['attr_groups'] = $attr_groups;
            }
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
        $query = BargainGoods::find()->alias('bg')
            ->leftJoin(['g' => Goods::tableName()], 'g.`id` = bg.`goods_id` AND g.`mall_id` = bg.`mall_id`')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.`id` = g.`goods_warehouse_id` AND  gw.`mall_id` = g.`mall_id`')
            ->where(['bg.mall_id' => \Yii::$app->mall->id, 'bg.is_delete' => 0])
            ->groupBy('bg.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'bg.end_time', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'bg.begin_time', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'bg.id desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new BargainStatisticsExport();
        $exp->export($query);
    }

    public function initData()
    {
        $query = BargainGoods::find()->with(['orderList.userOrderList'])->where([
            'mall_id' => \Yii::$app->mall->id
        ]);
        $limit = 10000;
        for ($i = 0; $i <= $query->count(); $i += $limit) {
            /* @var BargainGoods[] $list */
            $list = $query->limit($limit)->offset($i)->all();
            $goodsList = [];
            foreach ($list as $bargainGoods) {
                if (!isset($goodsList[$bargainGoods->id])) {
                    $goodsList[$bargainGoods->id] = [
                        'initiator' => 0,
                        'participant' => 0,
                        'min_price_goods' => 0,
                        'underway' => 0,
                        'success' => 0,
                        'fail' => 0,
                    ];
                }
                if ($bargainGoods->orderList && !empty($bargainGoods->orderList)) {
                    $goodsList[$bargainGoods->id]['initiator'] = count($bargainGoods->orderList);
                }
                foreach ($bargainGoods->orderList as $order) {
                    if ($order->status == Code::BARGAIN_PROGRESS) {
                        $goodsList[$bargainGoods->id]['underway'] += 1;
                    } elseif ($order->status == Code::BARGAIN_SUCCESS) {
                        $goodsList[$bargainGoods->id]['success'] += 1;
                    } else {
                        $goodsList[$bargainGoods->id]['fail'] += 1;
                    }
                    $goodsList[$bargainGoods->id]['participant'] += count($order->userOrderList);
                    $total = array_sum(array_column($order->userOrderList, 'price'));
                    $nowPrice = $order->getNowPrice($total);
                    if ($nowPrice == $order->min_price) {
                        $goodsList[$bargainGoods->id]['min_price_goods'] += 1;
                    }
                }
            }
            if (empty($goodsList)) {
                continue;
            }
            $ids = implode(',', array_keys($goodsList));
            $table = BargainGoods::tableName();
            $sql = "UPDATE {$table} SET `initiator` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $goods['initiator']);
            }
            $sql .= "END, `participant` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $goods['participant']);
            }
            $sql .= "END, `min_price_goods` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $goods['min_price_goods']);
            }
            $sql .= "END, `underway` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %.2f ", $id, $goods['underway']);
            }
            $sql .= "END, `success` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %.2f ", $id, $goods['success']);
            }
            $sql .= "END, `fail` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %.2f ", $id, $goods['fail']);
            }
            $sql .= "END WHERE `id` IN ($ids)";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }
}
