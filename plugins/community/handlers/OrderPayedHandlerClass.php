<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/13
 * Time: 15:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\handlers;




class OrderPayedHandlerClass extends \app\handlers\orderHandler\OrderPayedHandlerClass
{
    protected function notice()
    {
        \Yii::error('--community notice--');
        $this->sendTemplate()->sendMpTemplate()->sendBuyPrompt()->setGoods();
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--community pay--');
        $this->becomeJuniorByFirstPay()->becomeShare();
        return $this;
    }
}
