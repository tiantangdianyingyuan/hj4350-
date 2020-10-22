<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/11
 * Time: 10:24
 */

namespace app\plugins\vip_card\handlers;

use app\events\GoodsCatEvent;
use app\handlers\HandlerBase;
use app\models\GoodsCats;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;

class CatsDeleteHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(GoodsCats::EVENT_DESTROY, function ($event) {
            try {
                /**@var GoodsCatEvent $event**/
                /**@var VipCard $card**/
                $card = CommonVip::getCommon()->getMainCard();
                if (!$card) {
                    return;
                }

                $type = json_decode($card->type_info, true);
                if (!$type['cats']) {
                    return;
                }

                $res = array_intersect($event->catsList, $type['cats']);
                if ($res) {
                    foreach ($res as $v) {
                        $key = array_search($v, $type['cats']);
                        array_splice($type['cats'], $key, 1);
                    }
                } else {
                    return ;
                }

                $card->type_info = json_encode($type);
                $card->save();
            } catch (\Exception $e) {
                \Yii::error('超级会员卡删除指定商品失败');
                \Yii::error($e);
            }
        });
    }
}
