<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\handlers;


use app\handlers\HandlerBase;
use app\plugins\bargain\events\BargainGoodsEvent;
use app\plugins\bargain\jobs\BargainGoodsTimeJob;

class GoodsTimeHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BARGAIN_TIMER, function ($event) {
            /* @var BargainGoodsEvent $event */
            $time = strtotime($event->bargainGoods->end_time) - time();
            $time = $time < 0 ? 0 : $time;
            \Yii::$app->queue->delay($time)->push(new BargainGoodsTimeJob([
                'bargainGoods' => $event->bargainGoods
            ]));
        });
    }
}
