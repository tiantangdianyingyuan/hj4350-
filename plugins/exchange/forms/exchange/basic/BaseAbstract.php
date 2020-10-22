<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;


use app\models\User;

class BaseAbstract
{
    protected $config;
    protected $user;
    protected $codeModel;
    protected $extra_info;

    public function __construct(array $config, User $user, $codeModel, $extra_info)
    {
        $this->config = $config;
        $this->user = $user;
        $this->codeModel = $codeModel;
        $this->extra_info = $extra_info;
    }
}