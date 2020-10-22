<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\booking\models\BookingGoods;
use app\plugins\booking\Plugin;

class CommonBookingGoods extends Model
{
    public static function getGoods($goods_id)
    {
        $model = BookingGoods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'goods_id' => $goods_id
        ]);
        return $model;
    }

    /**
     * @return CommonBookingGoods
     */
    public static function getCommon()
    {
        return new self();
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '预约',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            $common = new CommonGoodsList();
            $common->sign = 'booking';
            $common->limit = 20;
            $common->status = 1;
            $common->relations = ['goodsWarehouse'];
            $list = $common->getList();
            $newList = [];
            foreach ($list as $item) {
                unset($item['attr']);
                unset($item['attr_groups']);
                $newList[] = $item;
            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
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
        $goodsId = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        /* @var BookingGoods[] $bookingGoodsList */
        $bookingGoodsList = BookingGoods::find()->with('goods.goodsWarehouse')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goodsId])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($bookingGoodsList as $bookingGoods) {
            $newItem = $common->getDiyBack($bookingGoods->goods);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
