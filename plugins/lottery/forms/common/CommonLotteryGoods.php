<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\forms\common\goods\CommonGoods;
use app\plugins\lottery\models\Goods;
use app\plugins\lottery\models\Lottery;

class CommonLotteryGoods extends Model
{
    public static function getGoods($goods_id, $mall_id = false)
    {
        if (!$mall_id) {
            $mall_id = \Yii::$app->mall->id;
        }
        $model = Lottery::findOne([
            'mall_id' => $mall_id,
            'goods_id' => $goods_id,
            'is_delete' => 0,
        ]);
        return $model;
    }

    //商品详情
    public static function getDetail($goods_id)
    {
        $commonGoods = CommonGoods::getCommon();
        $detail = $commonGoods->getGoodsDetail($goods_id);

        $price = [];
        $currency = [];
        foreach ($detail['attr'] as &$item) {
            $stepAttr = self::getAttr($item['goods_id'], $item['id']);
            $item['step_currency'] = $stepAttr['currency'] ?? 0;
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
        return new self();
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
        $goodsId = Goods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        /* @var Lottery[] $lotteryList */
        $lotteryList = Lottery::find()->with('goods.goodsWarehouse')
            ->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goodsId, 'status' => 1, 'type' => 0
            ])->andWhere(['>', 'end_at', mysql_timestamp()])->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($lotteryList as $lottery) {
            $newItem = $common->getDiyBack($lottery->goods);
            $newItem = array_merge($newItem, [
                'start_at' => $lottery->start_at,
                'end_at' => $lottery->end_at
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
