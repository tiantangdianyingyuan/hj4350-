<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\handler;


use app\handlers\orderHandler\OrderCreatedHandlerClass;

class OrderCreatedHandler extends OrderCreatedHandlerClass
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->setAutoCancel()->setShareUser()->setShareMoney();
    }
}