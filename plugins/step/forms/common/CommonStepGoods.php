<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\step\models\StepGoods;
use app\plugins\step\models\StepGoodsAttr;

class CommonStepGoods extends Model
{
    public static function getGoods($goods_id, $mall_id = false)
    {
        if (!$mall_id) {
            $mall_id = \Yii::$app->mall->id;
        }
        $model = StepGoods::findOne([
            'mall_id' => $mall_id,
            'is_delete' => 0,
            'goods_id' => $goods_id,
        ]);
        return $model;
    }

    public static function getAttr($goods_id, $attr_id, $mall_id = false)
    {
        if (!$mall_id) {
            $mall_id = \Yii::$app->mall->id;
        }
        $model = StepGoodsAttr::findOne([
            'mall_id' => $mall_id,
            'attr_id' => $attr_id,
            'goods_id' => $goods_id,
        ]);
        return $model;
    }

    //商品详情统一处理
    public static function getDetail($detail)
    {
        $price = [];
        $currency = [];
        foreach ($detail['attr'] as &$item) {
            $stepAttr = self::getAttr($item['goods_id'], $item['id']);
            $item['step_currency'] = $stepAttr['currency'] ?? 0;
            $item['extra'] = [
                'value'=> $stepAttr['currency'] ?? 0,
                'name' => CommonStep::getSetting()['currency_name'],
            ];
            array_push($price, $item['price']);
            array_push($currency, $item['step_currency']);
        }
        $detail['min_price'] = min($price);
        $detail['max_price'] = max($price);
        $detail['min_currency'] = min($currency);
        $detail['max_currency'] = max($currency);
        return $detail;
    }

    public static function getCommon()
    {
        $model = new self();
        return $model;
    }

    /**
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|StepGoods[]
     */
    public function getList($goodsIdList)
    {
        $list = StepGoods::find()
            ->where(['goods_id' => $goodsIdList, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->all();
        return $list;
    }

    /**
     * @param array $array
     * @return array
     * 获取diy商品列表信息
     */
    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        /* @var StepGoods[] $stepList */
        $goodsId = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $stepList = StepGoods::find()->with('goods.goodsWarehouse')
            ->where(['goods_id' => $goodsId, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($stepList as $stepGoods) {
            $newItem = $common->getDiyBack($stepGoods->goods);
            $newItem = array_merge($newItem, [
                'currency' => $stepGoods->currency
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
