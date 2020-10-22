<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-14
 * Time: 10:06
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common\combination;


use app\forms\api\order\OrderException;
use app\models\Goods;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;
use yii\helpers\ArrayHelper;

class GoodsCombination extends BaseCombination
{
    public function save()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $flag = false;
            foreach ($this->list as &$value) {
                if (isset($value['is_host']) && $value['is_host'] == 1) {
                    $flag = true;
                } else {
                    $value['is_host'] = 0;
                }
            }
            unset($value);
            if (!$flag) {
                throw new \Exception('搭配套餐必须选择主商品');
            }
            if (count($this->list) <= 1) {
                throw new \Exception('搭配套餐至少要有一个搭配商品');
            }
            $this->sort_price = 0;
            $minPrice = 0;
            foreach ($this->list as $value) {
                if ($value['is_host'] == 1) {
                    $this->sort_price = $value['discounts_price'];
                } else {
                    $temp = $minPrice == 0 ? $value['discounts_price'] : $minPrice;
                    $minPrice = min($temp, $value['discounts_price']);
                }
            }
            $this->sort_price += $minPrice;

            $model = $this->saveComposition();
            $this->id = $model->id;
            $this->saveGoods();
            $transaction->commit();
            return true;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    public function saveGoods()
    {
        $goodsIds = array_column($this->list, 'id');
        /* @var Goods[] $list */
        $list = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0,
            'sign' => '',
            'id' => $goodsIds,
        ])->with(['goodsWarehouse', 'attr'])
            ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->all();
        $goodsList = $this->getGoods($list);
        foreach ($goodsList as $goods) {
            if ($goods['is_delete'] != 0) {
                throw new \Exception('存在已被删除的商品（' . $goods['name'] . '），请先删除该商品在保存');
            }
            if ($goods['stock'] == 0) {
                throw new \Exception('存在库存为0的商品，请先添加该商品的库存');
            }
            foreach ($this->list as $value) {
                if ($value['id'] == $goods['id']) {
                    if ($value['discounts_price'] < 0) {
                        throw new \Exception('优惠金额不能小于0');
                    }
                    if ($value['discounts_price'] > $goods['min_price']) {
                        throw new \Exception('优惠金额不能大于商品最小金额');
                    }
                }
            }
        }
        CompositionGoods::updateAll(
            ['is_delete' => 1],
            ['mall_id' => $this->mall->id, 'model_id' => $this->id, 'is_delete' => 0]
        );
        foreach ($this->list as $value) {
            $model = new CompositionGoods();
            $model->mall_id = $this->mall->id;
            $model->model_id = $this->id;
            $model->goods_id = $value['id'];
            $model->price = $value['discounts_price'];
            $model->is_host = isset($value['is_host']) ? $value['is_host'] : 0;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
        }
        return true;
    }

    public function getOne()
    {
        /* @var Composition $model */
        $model = Composition::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'type' => 2])
            ->with(['compositionGoods.goods.attr', 'compositionGoods.goods.goodsWarehouse'])
            ->one();
        if (!$model) {
            throw new \Exception('错误的数据，请刷新重试');
        }
        $newList = [];
        $newHostList = [];
        foreach ($model->compositionGoods as $index => $value) {
            $newItem = ArrayHelper::toArray($value->goods);
            $newItem['attr_groups'] = json_decode($value->goods->attr_groups, true);
            $newItem['name'] = $value->goods->goodsWarehouse->name;
            $newItem['goodsWarehouse'] = $value->goods->goodsWarehouse;
            $newItem['goodsAttr'] = $this->getGoodsAttr($value);
            $newItem['stock'] = array_sum(array_column($newItem['goodsAttr'], 'stock'));
            $newItem['discounts_price'] = $value->price;
            $priceList = array_column($newItem['goodsAttr'], 'price');
            $newItem['min_price'] = !empty($priceList) ? min($priceList) : 0;
            $newItem['max_price'] = !empty($priceList) ? max($priceList) : 0;
            if ($value->is_host == 1) {
                $newItem['is_host'] = 1;
                $newHostList[] = $newItem;
            } else {
                $newItem['is_host'] = 0;
                $newList[] = $newItem;
            }
        }

        return [
            'name' => $model->name,
            'type' => $model->type,
            'price' => $model->price,
            'id' => $model->id,
            'list' => $newList,
            'host_list' => $newHostList,
        ];
    }

    public function getMaxDiscount()
    {
        return price_format(array_sum(array_column($this->composition->compositionGoods, 'price')));
    }

    public function getGoodsPrice($goodsId, $goodsAttrId, $attrPrice)
    {
        foreach ($this->composition->compositionGoods as $compositionGoods) {
            if ($compositionGoods->goods_id == $goodsId) {
                return $compositionGoods->price;
            }
        }
        throw new \Exception('套餐商品有变动，请重新选择套餐');
    }

    public function checkComposition($goodsList)
    {
        $compositionGoodsList = $this->composition->compositionGoods;
        $goodsIds = array_column($compositionGoodsList, 'goods_id');
        $flag = false;
        foreach ($goodsList as $goods) {
            if (!in_array($goods['id'], $goodsIds)) {
                throw new \Exception($this->composition->name . '套餐商品有变动，无法下单，请联系商户');
            }
            foreach ($compositionGoodsList as $compositionGoods) {
                if ($compositionGoods->goods->is_delete != 0) {
                    throw new \Exception($this->composition->name . '套餐商品有变动，无法下单，请联系商户');
                }
                if ($compositionGoods->goods_id == $goods['id'] && $compositionGoods->is_host == 1) {
                    $flag = true;
                    if (count($goodsList) <= 1) {
                        throw new \Exception($this->composition->name . '套餐未选择搭配商品');
                    }
                }
            }
        }
        if (!$flag) {
            throw new \Exception($this->composition->name . '套餐未选择主商品');
        }
        return true;
    }

    public function getGoodsDiscount($goodsId)
    {
        foreach ($this->composition->compositionGoods as $compositionGoods) {
            if ($compositionGoods->goods_id == $goodsId) {
                return $compositionGoods->price;
            }
        }
        return 0;
    }

    public function getCompositionPrice($goodsList)
    {
        $price = 0;
        foreach ($this->composition->compositionGoods as $compositionGoods) {
            $goodsIds = array_column($goodsList, 'id');
            if (in_array($compositionGoods->goods_id, $goodsIds)) {
                $price += floatval($compositionGoods->price);
            }
        }
        return $price;
    }
}
