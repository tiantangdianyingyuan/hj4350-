<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\exception;


class RollBackException extends \Exception
{
    public $token;

    public function __construct($message, $token)
    {
        $this->token = $token;
        parent::__construct($message);
    }

    public function getToken()
    {
        return $this->token;
    }
}