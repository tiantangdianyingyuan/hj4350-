<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/4
 * Time: 13:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\composition\forms\mall\StatisticsExport;
use app\plugins\composition\models\CompositionGoods;

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
        /* @var CompositionGoods[] $list */
        $query = CompositionGoods::find()->with(['goods.goodsWarehouse'])
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->andWhere(['>', 'payment_people', 0])
            ->keyword($this->date_start, ['>=', 'created_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'created_at', date(' Y-m-d H:i:s', strtotime($this->date_end) + 86399)])
            ->orderBy($this->order ? $this->order : 'id DESC')
            ->select([
                'sum(`payment_people`) as payment_people', 'sum(`payment_num`) as payment_num',
                'sum(`payment_amount`) as payment_amount', 'goods_id', 'created_at'
            ])
            ->groupBy('goods_id');
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }
        $list = $query
            ->page($pagination, 20, $this->page)
            ->all();
        $newList = [];
        foreach ($list as $key => $compositionGoods) {
            $newItem = [
                'cover_pic' => $compositionGoods->goods->goodsWarehouse->cover_pic,
                'name' => $compositionGoods->goods->goodsWarehouse->name,
                'payment_people' => $compositionGoods->payment_people,
                'payment_num' => $compositionGoods->payment_num,
                'payment_amount' => $compositionGoods->payment_amount,
            ];
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $newList,
            ]
        ];
    }


    protected function export($query)
    {
        $exp = new StatisticsExport();
        $exp->export($query);
    }
}
