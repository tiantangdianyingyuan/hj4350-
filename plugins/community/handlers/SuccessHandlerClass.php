<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/13
 * Time: 16:21
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\handlers;



class SuccessHandlerClass extends \app\handlers\orderHandler\OrderPayedHandlerClass
{
    protected function notice()
    {
        \Yii::error('--community success--');
        $this->sendSms()->sendMail()->receiptPrint('pay');
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--community success--');
        $this->saveResult();
        return $this;
    }

    protected function addShareOrder()
    {
        return $this;
    }

}
