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

class CatsEditHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(GoodsCats::EVENT_EDIT, function ($event) {
            /**@var GoodsCatEvent $event**/
            try {
                /**@var VipCard $card**/
                $card = CommonVip::getCommon()->getMainCard();
                if (!$card) {
                    return;
                }

                $type = json_decode($card->type_info, true);

                if ($type['all'] == true) {
                    $type['goods'] = [];
                    $type['cats'] = [];
                    if ($event->isVipCardCats == 0) {
                        if (isset($type['ignore_cats'])) {
                            if (!in_array($event->cats->id, $type['ignore_cats'])) {
                                $type['ignore_cats'] = array_merge($type['ignore_cats'], [(string)$event->cats->id]);
                            }
                        } else {
                            $type['ignore_cats'][] = (string)$event->cats->id;
                        }
                    } elseif ($event->isVipCardCats == 1) {
                        if (isset($type['ignore_cats'])) {
                            if (in_array($event->cats->id, $type['ignore_cats'])) {
                                $key = array_search($event->cats->id, $type['ignore_cats']);
                                array_splice($type['ignore_cats'], $key, 1);
                            }
                        } else {
                            $type['ignore_cats'] = [];
                        }
                    } else {
                        return;
                    }
                } else {
                    if ($event->isVipCardCats == 0) {
                        if (in_array($event->cats->id, $type['cats'])) {
                            $key = array_search($event->cats->id, $type['cats']);
                            array_splice($type['cats'], $key, 1);
                        }
                    } elseif ($event->isVipCardCats == 1) {
                        \Yii::error($event->cats->id);
                        \Yii::error($type['cats']);
                        \Yii::error(!in_array($event->cats->id, $type['cats']));
                        if (!in_array($event->cats->id, $type['cats'])) {
                            $type['cats'] = array_merge($type['cats'], [$event->cats->id]);
                        }
                    } else {
                        return;
                    }
                }

                $card->type_info = json_encode($type);
                $card->save();
            } catch (\Exception $exception) {
                \Yii::error('超级会员卡修改指定分类失败');
                \Yii::error($exception);
            }
        });
    }
}
