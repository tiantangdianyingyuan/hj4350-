<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-14
 * Time: 09:24
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common\combination;


use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;
use yii\helpers\ArrayHelper;

/**
 * Class BaseCombination
 * @package app\plugins\composition\forms\common\combination
 * @property Mall $mall
 * @property Composition $composition
 */
abstract class BaseCombination extends Model
{
    public $name;
    public $list;
    public $price;
    public $type;
    public $id;
    public $hostId; // 主商品id
    public $attrTotalPrice;
    public $sort_price;

    public $mall;
    public $composition;

    abstract public function save();

    /**
     * @return Composition|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     * 保存套餐
     */
    public function saveComposition()
    {
        if ($this->id) {
            $model = Composition::find()
                ->where([
                    'id' => $this->id, 'mall_id' => $this->mall->id
                ])->one();
        } else {
            $model = new Composition();
            $model->is_delete = 0;
            $model->type = $this->type;
            $model->mall_id = $this->mall->id;
        }
        $model->name = $this->name;
        $model->price = $this->price;
        $model->sort_price = $this->sort_price;
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        return $model;
    }

    public function getOne()
    {
        /* @var Composition $model */
        $model = Composition::find()->with('compositionGoods')
            ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'type' => $this->type])
            ->one();
        if (!$model) {
            throw new \Exception('错误的数据，请刷新重试');
        }
        $goodsIds = array_column($model->compositionGoods, 'goods_id');
        $list = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0,
            'sign' => '',
        ])->keyword($goodsIds && count($goodsIds) > 0, ['id' => $goodsIds])
            ->keyword($this->hostId, ['!=', 'id', $this->hostId])->with(['goodsWarehouse', 'attr'])
            ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->all();
        return [
            'name' => $model->name,
            'type' => $model->type,
            'price' => $model->price,
            'id' => $model->id,
            'list' => $this->getGoods($list)
        ];
    }

    public function getGoods($list)
    {
        $newList = [];
        /* @var Goods[] $list */
        foreach ($list as $index => $value) {
            $newItem = ArrayHelper::toArray($value);
            $newItem['attr_groups'] = json_decode($value->attr_groups, true);
            $newItem['name'] = $value->goodsWarehouse->name;
            $newItem['goodsWarehouse'] = $value->goodsWarehouse;
            $attrList = $value->resetAttr();
            $newAttr = [];
            foreach ($value->attr as $attr) {
                $newAttr[] = [
                    'attr_list' => $attrList[$attr->sign_id],
                    'price' => $attr->price,
                    'stock' => $attr->stock,
                    'goods_attr_id' => $attr->id,
                ];
            }
            $newItem['goodsAttr'] = $newAttr;
            $newItem['stock'] = array_sum(array_column($newItem['goodsAttr'], 'stock'));
            $newItem['discounts_price'] = '';
            $priceList = array_column($newItem['goodsAttr'], 'price');
            $newItem['min_price'] = !empty($priceList) ? min($priceList) : 0;
            $newItem['max_price'] = !empty($priceList) ? max($priceList) : 0;
            $newList[] = $newItem;
        }
        return $newList;
    }


    /**
     * @param Composition $composition
     * @return array
     * @throws \Exception
     */
    public function getGoodsList($composition)
    {
        $goodsList = [];
        $hostList = [];
        $hostPrice = 0;
        $minPrice = 0;
        $maxPrice = 0;
        $this->composition = $composition;
        $flag = false;
        $stock = null;
        foreach ($composition->compositionGoods as $compositionGoods) {
            if ($compositionGoods->goods->is_delete != 0) {
                $flag = true;
            }
            $goodsAttr = $this->getGoodsAttr($compositionGoods);
            $goods = [
                'id' => $compositionGoods->id,
                'goods_id' => $compositionGoods->goods_id,
                'name' => $compositionGoods->goods->goodsWarehouse->name,
                'cover_pic' => $compositionGoods->goods->goodsWarehouse->cover_pic,
                'is_host' => $compositionGoods->is_host,
                'price' => $compositionGoods->price,
                'min_price' => $flag ? 0 : min(array_column($goodsAttr, 'price')),
                'max_price' => $flag ? 0 : max(array_column($goodsAttr, 'price')),
                'attr_groups' => json_decode($compositionGoods->goods->attr_groups, true),
                'goods_attr' => $goodsAttr,
                'attr' => $goodsAttr,
                'stock' => array_sum(array_column($goodsAttr, 'stock')),
                'detail' => [
                    'price' => $compositionGoods->goods->price,
                    'attr' => $goodsAttr,
                    'sign' => 'composition',
                    'id' => $compositionGoods->goods->id,
                    'cover_pic' => $compositionGoods->goods->goodsWarehouse->cover_pic,
                    'goods_num' => $compositionGoods->goods->goods_stock,
                    'type' => $compositionGoods->goods->goodsWarehouse->type
                ],
                'type' => $compositionGoods->goods->goodsWarehouse->type
            ];
            $goods['min_composition_price'] = $flag ? -1 : $goods['min_price'] - $goods['price'];
            if ($compositionGoods->is_host == 1) {
                $hostPrice = $goods['price'];
                $hostList[] = $goods;
                $stock = $goods['stock'];
            } else {
                $goodsList[] = $goods;
                $minPrice = $minPrice > 0 ? min($minPrice, $goods['price']) : $goods['price'];
                $maxPrice += $goods['price'];
            }
        }
        if ($composition->type == 1) {
            $minCompositionPrice = array_sum(array_column($goodsList, 'min_price')) - $composition->price;
        } else {
            $minCompositionPrice = min(array_column($hostList, 'min_composition_price'));
            $minCompositionPrice = array_reduce(
                array_column($goodsList, 'min_composition_price'),
                function ($minCompositionPrice, $v) {
                    $minCompositionPrice += $v;
                    return $minCompositionPrice;
                },
                $minCompositionPrice
            );
        }

        return [
            'host_list' => $hostList,
            'goods_list' => $goodsList,
            'host_price' => $hostPrice,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'flag' => $flag,
            'stock' => $stock === null ? min(array_column($goodsList, 'stock')) : (max(array_column($goodsList, 'stock')) == 0 ? 0 : $stock),
            'min_composition_price' => price_format($minCompositionPrice)
        ];
    }

    /**
     * @param CompositionGoods $compositionGoods
     * @return array
     * @throws \Exception
     */
    public function getGoodsAttr($compositionGoods)
    {
        $goods = $compositionGoods->goods;
        $attrList = $goods->resetAttr();
        $newAttr = [];
        foreach ($goods->attr as $attr) {
            $newAttr[] = [
                'attr_list' => $attrList[$attr->sign_id],
                'goods_attr_id' => $attr->id,
                'price' => $attr->price,
                'stock' => $attr->stock
            ];
        }
        return $newAttr;
    }

    public function getMaxDiscount()
    {
        return 0;
    }

    /**
     * @param $goodsId
     * @param $goodsAttrId
     * @param $attrPrice
     * @return int
     * 获取套餐商品的优惠金额
     */
    public function getGoodsPrice($goodsId, $goodsAttrId, $attrPrice)
    {
        return 0;
    }

    public function getCompositionGoods($goodsId)
    {
        foreach ($this->composition->compositionGoods as $compositionGoods) {
            if ($compositionGoods->goods_id == $goodsId) {
                return $compositionGoods->id;
            }
        }
        throw new \Exception('套餐中商品被删除，请重新下单');
    }

    /**
     * @param $goodsList
     * @return bool
     * @throws \Exception
     * 检测套餐是否可以购买
     */
    public function checkComposition($goodsList)
    {
        return true;
    }

    /**
     * @param $goodsAttr
     * @param $subNum
     * @param $goodsItem
     * @return bool
     * 套餐库存减少
     */
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return true;
    }

    /**
     * @param $goodsId
     * @return int
     * 获取某个商品的最多优惠金额
     */
    public function getGoodsDiscount($goodsId)
    {
        return 0;
    }

    /**
     * @param $goodsList
     * @return int
     * 获取套餐优惠金额
     */
    public function getCompositionPrice($goodsList)
    {
        return 0;
    }
}
