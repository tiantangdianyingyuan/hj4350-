<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/2/28
 * Time: 9:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\handlers;


use app\handlers\HandlerBase;
use app\models\Goods;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;

class GoodsDestroyHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Goods::EVENT_DESTROY, function ($event) {
            try {
                \Yii::warning('套餐商品被删除时，套餐下架');
                /* @var Goods $goods */
                $goods = $event->goods;
                $compositionIds = CompositionGoods::find()->where([
                    'mall_id' => $goods->mall_id, 'is_delete' => 0, 'goods_id' => $goods->id
                ])->select('model_id')->column();
                $res = Composition::updateAll(['status' => 0], ['mall_id' => $goods->mall_id, 'id' => $compositionIds]);
                \Yii::warning('共有' . $res . '个套餐因商品' . $goods->id . '被下架');
            } catch (\Exception $exception) {
                \Yii::warning('套餐商品删除失败');
                \Yii::warning($exception);
            }
        });
    }
}
