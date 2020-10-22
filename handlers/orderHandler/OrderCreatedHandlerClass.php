<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 10:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;

class OrderCreatedHandlerClass extends BaseOrderCreatedHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->setAutoCancel()->setShareUser()->setShareMoney()->receiptPrint('order')->deleteCartGoods();
    }
}
