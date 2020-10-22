<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;

class Balance extends BaseAbstract implements Base
{
    public function exchange(&$message)
    {
        try {
            $balance = floatval($this->config['balance']);
            $desc = sprintf('兑换码%s兑换%s余额', $this->codeModel->code, $balance);
            return \Yii::$app->currency->setUser($this->user)->balance->add($balance, $desc) === true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
