<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 14:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\handlers;


use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\forms\common\template\tplmsg\Tplmsg;
use app\handlers\HandlerBase;
use app\models\User;
use app\plugins\bargain\events\BargainUserOrderEvent;
use app\plugins\bargain\forms\common\BargainSuccessTemplate;

class BargainUserJoinHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BARGAIN_USER_JOIN, function ($event) {
            /* @var BargainUserOrderEvent $event */
            $bargainUserOrderAll = $event->bargainUserOrderAll;
            $bargainOrder = $event->bargainOrder;
            $totalPrice = 0;
            foreach ($bargainUserOrderAll as $bargainUserOrder) {
                $totalPrice += floatval($bargainUserOrder->price);
            }
            if ($bargainOrder->price - $bargainOrder->min_price <= $totalPrice) {
                $user = User::findOne(['id' => $bargainOrder->user_id]);
                $pageUrl = 'plugins/bargain/activity/activity?id=' . $bargainOrder->id;
                $tplMsg = new BargainSuccessTemplate([
                    'page' => $pageUrl,
                    'user' => $user,
                    'goodsName' => $bargainOrder->goodsWarehouse->name,
                    'price' => $bargainOrder->price . '元',
                    'minPrice' => $bargainOrder->min_price . '元',
                    'remark' => '已砍至最低价，快来买我吧！！'
                ]);
                $tplMsg->send();
            }
        });
    }
}
