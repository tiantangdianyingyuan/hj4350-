<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/1/15
 * Time: 14:07
 */


namespace app\plugins\region\handlers;

use app\forms\common\template\tplmsg\RemoveIdentityTemplate;
use app\handlers\HandlerBase;
use app\models\User;
use app\plugins\region\events\RegionEvent;
use app\plugins\region\models\RegionUser;

class RemoveRegionHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(
            RegionUser::EVENT_REMOVE,
            function ($event) {
                /**
                 * @var RegionEvent $event
                 */

                try {
                    $user = User::findOne(
                        [
                            'id' => $event->region->user_id,
                            'mall_id' => $event->region->mall_id,
                            'is_delete' => 0
                        ]
                    );

                    $time = date('Y-m-d H:i:s', time());
                    $tplMsg = new RemoveIdentityTemplate(
                        [
                            'page' => 'plugins/region/index/index',
                            'user' => $user,
                            'remark' => "区域代理解除:" . ($event->region->regionInfo->reason ?? '你的区域身份已被解除'),
                            'time' => $time
                        ]
                    );
                    $tplMsg->send();
                } catch (\Exception $exception) {
                    \Yii::error("发送区域模板消息失败");
                    \Yii::error($exception);
                }

                return true;
            }
        );
    }
}
