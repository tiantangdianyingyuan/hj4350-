<?php

namespace app\plugins\pick\forms\common;

use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\pick\forms\mall\SettingForm;
use app\plugins\pick\models\Goods;
use app\plugins\pick\models\PickGoods;
use yii\db\Query;

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
        $setting = (new SettingForm())->search();
        /* @var PickGoods[] $pickGoodsList */
        $goodsIdList = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $pickGoodsList = PickGoods::find()->alias('p')->with('goods.goodsWarehouse')
            ->where(['p.is_delete' => 0, 'p.mall_id' => \Yii::$app->mall->id, 'p.goods_id' => $goodsIdList])
            ->joinWith(['activity a' => function ($query) {
                $query->where([
                    'AND',
                    ['a.mall_id' => \Yii::$app->mall->id],
                    ['a.is_delete' => 0],
                    ['a.status' => 1],
                    ['<=', 'a.start_at', date('Y-m-d H:i:s')],
                    ['>=', 'a.end_at', date('Y-m-d H:i:s')],
                ]);
            }])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($pickGoodsList as $pickGoods) {
            $newItem = $common->getDiyBack($pickGoods->goods);
            $newItem['rule_price'] = $pickGoods->activity->rule_price;
            $newItem['rule_num'] = $pickGoods->activity->rule_num;
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|PickGoods[]
     */
    public function getList($goodsIdList)
    {
        $pickGoods = PickGoods::find()
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'goods_id' => $goodsIdList])
            ->andWhere([
                'and',
                ['<=', 'start_at', date('Y-m-d H:i:s')],
                ['>=', 'end_at', date('Y-m-d H:i:s')],
            ])
            ->all();
        return $pickGoods;
    }
}
