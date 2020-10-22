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
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\forms\common\MsgService;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionUser;

class LevelUpRegionHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(
            RegionUser::EVENT_LEVEL_UP,
            function ($event) {
                $user = User::findOne(
                    [
                        'id' => $event->region->user_id,
                        'mall_id' => $event->region->mall_id,
                        'is_delete' => 0
                    ]
                );
                $common = new CommonRegion();
                /**
                 * @var RegionEvent $event
                 */
                try {
                    $time = date('Y-m-d H:i:s', time());
                    $tplMsg = new RemoveIdentityTemplate(
                        [
                            'page' => 'plugins/region/index/index',
                            'user' => $user,
                            'remark' => "代理级别升级:" .
                                $common->parseLevel($event->originLevel) . "=>" . $common->parseLevel(
                                    $event->region->level
                                ),
                            'time' => $time
                        ]
                    );
                    $tplMsg->send();
                } catch (\Exception $exception) {
                    \Yii::info("发送区域代理升级模板消息失败");
                    \Yii::info($exception);
                }

                try {
                    $level = RegionArea::find()
                        ->where(['id' => $event->region->area_id])
                        ->one();
                    $rate = 0;
                    switch ($event->region->level) {
                        case 1:
                            $rate = $level->province_rate;
                            break;
                        case 2:
                            $rate = $level->city_rate;
                            break;
                        case 3:
                            $rate = $level->district_rate;
                            break;
                        default:
                            break;
                    }
                    $mobile = $event->region->regionInfo->phone ?? $user->mobile;
                    MsgService::sendSms($mobile, 2, $common->parseLevel($event->region->level), $rate);
                } catch (\Exception $exception) {
                    \Yii::info("发送区域代理升级短信失败");
                    \Yii::info($exception);
                }
                return true;
            }
        );
    }
}
