<?php


namespace app\plugins\lottery\forms\api;

use app\forms\mall\export\LotteryStatisticsExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\lottery\models\Lottery;

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


        $query->select(["gw.`name`,gw.`cover_pic`,g.`attr_groups`,l.`start_at`,l.`end_at`,
                        l.`participant`,l.`invitee`,l.`code_num`,
                        CASE WHEN l.`start_at` > NOW() THEN '未开始' WHEN l.`end_at` < NOW() THEN '已结束' ELSE '进行中' END AS `status`"]);
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
        $query = Lottery::find()->alias('l')
            ->leftJoin(['g' => Goods::tableName()], 'g.`id` = l.`goods_id`')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.`goods_warehouse_id`')
            ->where(['l.mall_id' => \Yii::$app->mall->id, 'l.is_delete' => 0])
            ->groupBy('l.`id`');
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'l.end_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'l.start_at', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'gw.name', $this->name]);
        }

        $query->orderBy(!empty($this->order) ? $this->order : 'l.id desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new LotteryStatisticsExport();
        $exp->export($query);
    }

    public function initData()
    {
        $query = Lottery::find()->with('log')->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        $limit = 10000;
        for ($i = 0; $i <= $query->count(); $i += $limit) {
            /* @var Lottery[] $list */
            $list = $query->limit($limit)->offset($i)->all();
            $lotteryList = [];
            foreach ($list as $lottery) {
                if (!$lottery->log || empty($lottery->log)) {
                    continue;
                }
                if (!isset($lotteryList[$lottery->id])) {
                    $lotteryList[$lottery->id] = [
                        'participant' => 0,
                        'code_num' => 0,
                        'invitee' => 0,
                    ];
                }
                foreach ($lottery->log as $log) {
                    if ($log->child_id == 0) {
                        $lotteryList[$lottery->id]['participant'] += 1;
                    }
                    if ($log->status != 0) {
                        $lotteryList[$lottery->id]['code_num'] += 1;
                    }
                    if ($log->child_id != 0) {
                        $lotteryList[$lottery->id]['invitee'] += 1;
                    }
                }
            }
            if (empty($lotteryList)) {
                continue;
            }
            $ids = implode(',', array_keys($lotteryList));
            $table = Lottery::tableName();
            $sql = "UPDATE {$table} SET `invitee` = CASE `id` ";
            foreach ($lotteryList as $id => $lottery) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $lottery['invitee']);
            }
            $sql .= "END, `code_num` = CASE `id` ";
            foreach ($lotteryList as $id => $lottery) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $lottery['code_num']);
            }
            $sql .= "END, `participant` = CASE `id` ";
            foreach ($lotteryList as $id => $lottery) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $lottery['participant']);
            }
            $sql .= "END WHERE `id` IN ($ids)";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return true;
    }
}
