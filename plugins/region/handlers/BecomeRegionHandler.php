<?php

/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:37
 */

namespace app\plugins\region\handlers;

use app\handlers\HandlerBase;
use app\models\DistrictArr;
use app\models\User;
use app\plugins\region\events\RegionEvent;
use app\plugins\region\forms\common\MsgService;
use app\plugins\region\models\RegionUser;

class BecomeRegionHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(
            RegionUser::EVENT_BECOME,
            function ($event) {
                /**
                 * @var RegionEvent $event
                 */
                $user = User::findOne(
                    [
                        'id' => $event->region->user_id,
                        'mall_id' => $event->region->mall_id,
                        'is_delete' => 0
                    ]
                );

                MsgService::sendTpl($user, $event);
                $mobile = $event->region->regionInfo->phone ?? $user->mobile;
                $province = $newItem['attr'] = DistrictArr::getDistrict($event->region->province_id)['name'];
                if ($event->region->level == 3) {
                    $parent_id = DistrictArr::getDistrict($event->region->regionRelation[0]->district_id)['parent_id'];
                    $name = $province . DistrictArr::getDistrict($parent_id)['name'];
                } else {
                    $name = $province;
                }
                if ($event->region->status == 1) {
                    MsgService::sendSms($mobile, 1, $name);
                }
            }
        );
    }
}
