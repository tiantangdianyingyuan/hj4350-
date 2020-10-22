<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/1/15
 * Time: 14:07
 */


namespace app\plugins\stock\handlers;

use app\forms\common\template\tplmsg\RemoveIdentityTemplate;
use app\handlers\HandlerBase;
use app\models\User;
use app\plugins\stock\events\StockEvent;
use app\plugins\stock\forms\common\MsgService;
use app\plugins\stock\models\StockUser;

class RemoveStockHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(StockUser::EVENT_REMOVE, function ($event) {
            /**
             * @var StockEvent $event
             */

            try {
                $user = User::findOne([
                    'id' => $event->stock->user_id,
                    'mall_id' => $event->stock->mall_id,
                    'is_delete' => 0
                ]);

                $time = date('Y-m-d H:i:s', time());
                $tplMsg = new RemoveIdentityTemplate([
                    'page' => 'plugins/stock/index/index',
                    'user' => $user,
                    'remark' => "股东解除:" . ($event->stock->stockInfo->reason ?? '你的股东身份已被解除'),
                    'time' => $time
                ]);
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::error("发送股东订阅消息失败");
                \Yii::error($exception);
            }

            return true;
        });
    }
}
