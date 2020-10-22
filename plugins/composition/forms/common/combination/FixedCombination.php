<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-14
 * Time: 09:54
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common\combination;


use app\forms\api\order\OrderException;
use app\models\Goods;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;

class FixedCombination extends BaseCombination
{
    public function save()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (count($this->list) <= 1) {
                throw new \Exception('固定套餐商品必须大于等于两个');
            }
            $this->sort_price = $this->price;
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
        }
        $minPrice = array_sum(array_column($goodsList, 'min_price'));
        if ($minPrice < $this->price) {
            throw new \Exception('优惠金额不能大于所有商品最小值之和');
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
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
        }
        return true;
    }

    public function getMaxDiscount()
    {
        return price_format($this->composition->price);
    }

    public function getGoodsPrice($goodsId, $goodsAttrId, $attrPrice)
    {
        if ($this->attrTotalPrice !== null) {
            return $attrPrice * $this->composition->price / $this->attrTotalPrice;
        } else {
            return 0;
        }
    }

    public function checkComposition($goodsList)
    {
        $compositionGoodsList = $this->composition->compositionGoods;
        if (count($compositionGoodsList) != count($goodsList)) {
            throw new \Exception($this->composition->name . '套餐商品有变动，无法下单，请联系商户');
        }
        $goodsIds = array_column($compositionGoodsList, 'goods_id');
        foreach ($compositionGoodsList as $compositionGoods) {
            if ($compositionGoods->goods->is_delete != 0) {
                throw new \Exception($this->composition->name . '套餐商品有变动，无法下单，请联系商户');
            }
        }
        foreach ($goodsList as $goods) {
            if (!in_array($goods['id'], $goodsIds)) {
                throw new \Exception($this->composition->name . '套餐商品有变动，无法下单，请联系商户');
            }
        }
        return true;
    }

    public function getGoodsDiscount($goodsId)
    {
        $goodsPrice = 0;
        $totalPrice = 0;
        foreach ($this->composition->compositionGoods as $compositionGoods) {
            if ($compositionGoods->goods->is_delete != 0) {
                continue;
            }
            $goodsAttr = $this->getGoodsAttr($compositionGoods);
            if ($compositionGoods->goods_id == $goodsId) {
                $goodsPrice = max(array_column($goodsAttr, 'price'));
                $totalPrice += $goodsPrice;
            } else {
                $totalPrice += min(array_column($goodsAttr, 'price'));
            }
        }
        return $goodsPrice ? price_format($this->composition->price * $goodsPrice / $totalPrice) : 0;
    }

    public function getCompositionPrice($goodsList)
    {
        return $this->composition->price;
    }
}
