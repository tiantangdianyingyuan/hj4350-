<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/20
 * Time: 9:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\mall;


use app\core\response\ApiCode;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\Goods;

class GoodsListForm extends Model
{
    public $keyword;
    public $status;
    public $date_start;
    public $date_end;
    public $sort_prop;
    public $sort_type;
    public $page;
    public $type;

    public function rules()
    {
        return [
            [['keyword', 'date_start', 'date_end', 'sort_prop', 'type'], 'trim'],
            [['keyword', 'date_start', 'date_end', 'sort_prop', 'type'], 'string'],
            [['status', 'sort_type', 'page'], 'integer'],
            ['status', 'default', 'value' => -1],
            ['page', 'default', 'value' => 1],
            ['sort_prop', 'in', 'range' => ['id', 'goods_stock']],
            ['sort_type', 'in', 'range' => [0, 1]],
            ['type', 'in', 'range' => ['', 'goods', 'ecard']]
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort_prop' => '需要排序的字段名称',
            'sort_type' => '排序方式',
            'status' => '状态值',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $bargainGoodsCondition = [];
        $goodsWarehouseCondition = [];
        $condition = [];
        if ($this->type !== '') {
            $goodsWarehouseIds = GoodsWarehouse::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere(['type' => $this->type])
                ->select('id');
            $goodsWarehouseCondition[] = ['goods_warehouse_id' => $goodsWarehouseIds];
        }
        if ($this->keyword !== '') {
            $goodsWarehouseIds = GoodsWarehouse::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere(['like', 'name', $this->keyword])
                ->select('id');
            $goodsWarehouseCondition[] = [
                'or',
                ['like', 'g.id', $this->keyword],
                ['g.goods_warehouse_id' => $goodsWarehouseIds]
            ];
        }
        if (!empty($goodsWarehouseCondition)) {
            array_unshift($goodsWarehouseCondition, 'and');
        }
        $nowDate = mysql_timestamp();
        $dateStart = null;
        $dateEnd = null;
        if ($this->date_start !== '' && $this->date_end !== '') {
            $dateStart = $this->date_start;
            $dateEnd = $this->date_end;
            $bargainGoodsCondition[] = [
                'or',
                ['between', 'begin_time', $dateStart, $dateEnd],
                ['between', 'end_time', $dateStart, $dateEnd],
                [
                    'and',
                    ['>=', 'begin_time', $dateStart],
                    ['<=', 'end_time', $dateEnd]
                ],
                [
                    'and',
                    ['<=', 'begin_time', $dateStart],
                    ['>=', 'end_time', $dateEnd]
                ]
            ];
        }
        switch ($this->status) {
            case 0:
                $condition = ['g.status' => 0];
                break;
            case 1:
                $condition = ['g.status' => 1];
                $bargainGoodsCondition[] = ['>=', 'begin_time', $nowDate];
                break;
            case 2:
                $condition = ['g.status' => 1];
                $bargainGoodsCondition[] = ['<=', 'begin_time', $nowDate];
                $bargainGoodsCondition[] = ['>=', 'end_time', $nowDate];
                break;
            case 3:
                $condition = ['g.status' => 1];
                $bargainGoodsCondition[] = ['<=', 'end_time', $nowDate];
                break;
            default:
        }
        if (!empty($bargainGoodsCondition)) {
            array_unshift($bargainGoodsCondition, 'and');
            $bargainGoodsId = BargainGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere($bargainGoodsCondition)
                ->select('goods_id');
            $bargainGoodsCondition = ['g.id' => $bargainGoodsId];
        }
        $sortType = [SORT_DESC, SORT_ASC];
        switch ($this->sort_prop) {
            case 'id':
                $orderBy = ['g.id' => $sortType[$this->sort_type], 'g.created_at' => SORT_DESC];
                break;
            case 'goods_stock':
                $orderBy = ['bargain_goods_stock' => $sortType[$this->sort_type], 'g.created_at' => SORT_DESC];
                break;
            default:
                $orderBy = ['g.created_at' => SORT_DESC, 'g.id' => SORT_ASC];
        }
        /* @var Goods[] $list */
        $list = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr', 'bargainGoods'])
            ->where(['g.mall_id' => \Yii::$app->mall->id, 'g.is_delete' => 0, 'g.sign' => 'bargain', 'g.mch_id' => 0])
            ->keyword(!empty($condition), $condition)
            ->keyword(!empty($bargainGoodsCondition), $bargainGoodsCondition)
            ->keyword(!empty($goodsWarehouseCondition), $goodsWarehouseCondition)
            ->leftJoin(['bg' => BargainGoods::tableName()], 'bg.goods_id=g.id')
            ->select('g.*,bg.stock bargain_goods_stock')
            ->orderBy($orderBy)
            ->page($pagination, 20, $this->page)
            ->all();
        $newList = [];
        foreach ($list as $goods) {
            if ($goods->status == 0) {
                $statusText = 4;
            } else {
                if ($goods->bargainGoods->begin_time > $nowDate) {
                    $statusText = 1;
                } elseif ($goods->bargainGoods->end_time > $nowDate) {
                    $statusText = 2;
                } else {
                    $statusText = 3;
                }
            }
            $newList[] = [
                'id' => $goods->id,
                'mch_id' => $goods->mch_id,
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'status' => $goods->status,
                'virtual_sales' => $goods->virtual_sales,
                'created_at' => $goods->created_at,
                'sign' => $goods->sign,
                'sort' => $goods->sort,
                'goodsWarehouse' => [
                    'id' => $goods->goodsWarehouse->id,
                    'name' => $goods->goodsWarehouse->name,
                    'cover_pic' => $goods->goodsWarehouse->cover_pic,
                ],
                "name" => $goods->goodsWarehouse->name,
                "price" => $goods->price,
                "goods_stock" => $goods->bargainGoods->stock,
                'num_count' => $goods->bargainGoods->stock,
                'begin_time' => $goods->bargainGoods->begin_time,
                'end_time' => $goods->bargainGoods->end_time,
                'min_price' => $goods->bargainGoods->min_price,
                'status_text' => $statusText,
                'attr_groups' => \Yii::$app->serializer->decode($goods->attr_groups),
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'goods_count' => $pagination->totalCount
            ]
        ];
    }
}
