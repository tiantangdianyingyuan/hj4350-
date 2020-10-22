<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api\v2;

use app\core\response\ApiCode;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\Plugin;

class CatsForm extends Model
{
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            // 有阶梯团的商品 才在前端展示
            $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])
                ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->groupBy('pintuan_goods_id')
                ->select('pintuan_goods_id');
            $query = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $pintuanGoodsIds,])
                ->andWhere(['<', 'start_time', mysql_timestamp()])
                ->andWhere([
                    'or',
                    ['end_time' => '0000-00-00 00:00:00'],
                    ['>', 'end_time', mysql_timestamp()]
                ]);
            $goodsIds = $query->select('goods_id');

            $goodsWarehouseIds = Goods::find()->where([
                'id' => $goodsIds,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'sign' => (new Plugin)->getName(),
                'status' => 1,
            ])->select('goods_warehouse_id');

            $catIds = GoodsCatRelation::find()->where([
                'is_delete' => 0,
                'goods_warehouse_id' => $goodsWarehouseIds,
            ])->select('cat_id');

            $catList = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => 0,
                'is_delete' => 0,
                'status' => 1,
            ])
            ->andWhere([
                'or',
                ['id' => $catIds],
                ['parent_id' => $catIds]
            ])
            ->orderBy(['sort' => SORT_ASC])->all();

            $parentIds = [];
            foreach ($catList as $item) {
                if ($item->parent_id) {
                    $parentIds[] = $item->parent_id;
                } else {
                    $parentIds[] = $item->id;
                }
            }

            $catList = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => 0,
                'is_delete' => 0,
                'status' => 1,
            ])
            ->andWhere([
                'or',
                ['id' => $parentIds],
                ['parent_id' => $parentIds]
            ])
                ->orderBy(['sort' => SORT_ASC])->all();


            $newCatList = [];
            /** @var GoodsCats $cat */
            foreach ($catList as $cat) {
                if ($cat->parent_id != 0) {
                    continue;
                }
                $newCatItem = [];
                $newCatItem['id'] = $cat->id;
                $newCatItem['name'] = $cat->name;
                $newCatList[] = $newCatItem;
            }

            array_unshift($newCatList, ['id' => -1, 'name' => '全部']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'list' => $newCatList
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}
