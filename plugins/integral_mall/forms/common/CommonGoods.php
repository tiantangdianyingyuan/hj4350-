<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/11
 * Time: 11:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\integral_mall\forms\common;


use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallGoods;

class CommonGoods extends Model
{
    public static function getCommon()
    {
        $model = new self();
        return $model;
    }

    /**
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|IntegralMallGoods[]
     */
    public function getList($goodsIdList)
    {
        $list = IntegralMallGoods::find()
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
        /* @var IntegralMallGoods[] $goodsList */
        $goodsId = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $goodsList = IntegralMallGoods::find()->with('goods.goodsWarehouse')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goodsId])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($goodsList as $integralMallGoods) {
            $newItem = $common->getDiyBack($integralMallGoods->goods);
            $newItem = array_merge($newItem, [
                'integral' => $integralMallGoods->integral_num
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
