<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/10
 * Time: 16:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;

use app\events\ShareMemberEvent;
use app\forms\common\share\CommonShareLevel;
use app\jobs\ChangeParentJob;

class ChangeShareMemberHandle extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::CHANGE_SHARE_MEMBER, function ($event) {
            /* @var ShareMemberEvent $event */
            \Yii::$app->queue->delay(0)->push(new ChangeParentJob([
                'mall' => $event->mall,
                'beforeParentId' => $event->beforeParentId,
                'parentId' => $event->parentId,
                'user_id' => $event->userId
            ]));
            try {
                $commonShareLevel = CommonShareLevel::getInstance($event->mall);
                // 改变前的分销商分销等级修改
                $commonShareLevel->userId = $event->beforeParentId;
                $commonShareLevel->levelShare(CommonShareLevel::CHILDREN_COUNT);
            } catch (\Exception $exception) {
                \Yii::error('分销等级修改出错：');
                \Yii::error($exception);
            }
            try {
                $commonShareLevel = CommonShareLevel::getInstance($event->mall);
                // 改变后的分销商分销等级修改
                $commonShareLevel->userId = $event->parentId;
                $commonShareLevel->levelShare(CommonShareLevel::CHILDREN_COUNT);
            } catch (\Exception $exception) {
                \Yii::error('分销等级修改出错：');
                \Yii::error($exception);
            }
            return true;
        });
    }
}
