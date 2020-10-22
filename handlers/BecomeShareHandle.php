<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/22
 * Time: 15:03
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;


use app\events\ShareEvent;
use app\forms\common\template\tplmsg\ShareAudiTemplate;

class BecomeShareHandle extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BECOME_SHARE, function ($event) {
            /* @var ShareEvent $event */
            if ($event->share->status == 1 || $event->share->status == 2) {
                $tplMsg = new ShareAudiTemplate([
                    'page' => 'pages/share/index/index',
                    'user' => $event->share->user,
                    'reviewProject' => '分销商审核',
                    'result' => $event->share->getStatusText($event->share->status),
                    'nickname' => $event->share->user->nickname,
                    'time' => $event->share->updated_at
                ]);
                $tplMsg->send();
            }
            return true;
        });
    }
}
