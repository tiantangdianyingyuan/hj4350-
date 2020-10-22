<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/11
 * Time: 10:20
 */

namespace app\plugins\vip_card\handlers;

use app\events\GoodsEvent;
use app\handlers\HandlerBase;
use app\models\Goods;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardAppointGoods;

class GoodsEditHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Goods::EVENT_EDIT, function ($event) {
            /**@var GoodsEvent $event**/
            try {
                $card = CommonVip::getCommon()->getMainCard();
                if (!$card) {
                    return;
                }

                $appoint = VipCardAppointGoods::find()->where(['goods_id' => $event->goods->id])->one();
                if ( $event->isVipCardGoods == 0) {
                    if ($appoint) {
                        $appoint->delete();
                    }
                } elseif ($event->isVipCardGoods == 1) {
                    if (!$appoint) {
                        $appoint = new VipCardAppointGoods();
                        $appoint->goods_id = $event->goods->id;
                        $appoint->save();
                    }
                } else {
                    return;
                }
            } catch (\Exception $exception) {
                \Yii::error('超级会员卡修改指定商品失败');
                \Yii::error($exception);
            }
        });
    }
}
