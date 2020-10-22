<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/11
 * Time: 9:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\advance\forms\common;


use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\Goods;

class CommonGoods extends Model
{
    public static function getCommon()
    {
        $model = new self();
        return $model;
    }

    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        /* @var AdvanceGoods[] $advanceGoodsList */
        $goodsIdList = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $advanceGoodsList = AdvanceGoods::find()->with('goods.goodsWarehouse')
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'goods_id' => $goodsIdList])
            ->andWhere(['<=', 'start_prepayment_at', date('Y-m-d H:i:s', time())])
            ->andWhere(['>=', 'end_prepayment_at', date('Y-m-d H:i:s', time())])
            ->with(['attr.attr', 'goods'])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($advanceGoodsList as $advanceGoods) {
            $newItem = $common->getDiyBack($advanceGoods->goods);
            if ($advanceGoods['goods']['use_attr'] == 1) {
                $minDeposit = $advanceGoods['attr'][0]['attr']['deposit'];
                $minSwellDeposit = $advanceGoods['attr'][0]['attr']['swell_deposit'];
                foreach ($advanceGoods['attr'] as $k => $v) {
                    if ($minDeposit < $v['attr']['deposit']) {
                        $minDeposit = $v['attr']['deposit'];
                        $minSwellDeposit = $v['attr']['swell_deposit'];
                    }
                }
            } else {
                $minDeposit = $advanceGoods->deposit;
                $minSwellDeposit = $advanceGoods->swell_deposit;
            }
            $newItem = array_merge($newItem, [
                'deposit' => $minDeposit,
                'swell_deposit' => $minSwellDeposit,
                'advanceGoods' => $advanceGoods,
                'goods' => $advanceGoods->goods
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
