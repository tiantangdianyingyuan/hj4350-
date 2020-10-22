<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/5
 * Time: 9:42
 */

namespace app\plugins\flash_sale\handlers;

use app\handlers\HandlerBase;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\FlashSaleGoodsAttr;
use Yii;

class GoodsDestroyHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        Yii::$app->on(
            Goods::EVENT_DESTROY,
            function ($event) {
                if ($event->goods->sign == '') {
                    $ids = Goods::find()
                        ->alias('g')
                        ->select(['g.id'])
                        ->innerJoin(['gw' => GoodsWarehouse::tableName()], 'g.goods_warehouse_id = gw.id')
                        ->where(
                            [
                                'g.mall_id' => Yii::$app->mall->id,
                                'g.goods_warehouse_id' => $event->goods->goods_warehouse_id,
                                'g.sign' => 'flash_sale'
                            ]
                        )
                        ->column();

                    FlashSaleGoods::updateAll(
                        [
                            'is_delete' => 1,
                        ],
                        [
                            'goods_id' => $ids,
                            'mall_id' => $event->goods->mall_id
                        ]
                    );

                    FlashSaleGoodsAttr::updateAll(
                        [
                            'is_delete' => 1,
                        ],
                        [
                            'goods_id' => $ids,
                        ]
                    );
                }
            }
        );
    }
}
