<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/8/13
 * Time: 16:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;


class OrderChangePriceHandlerClass extends BaseOrderHandler
{
    public function handle()
    {
        \Yii::error('--改价事件触发--');
        $this->addShareOrder();
    }
}
