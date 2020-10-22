<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\validate;

class FacadeAdmin
{
    public $validate;

    public function __construct()
    {
        $this->validate = new Validate();
    }

    //后台
    public function admin($mall_id, $code)
    {
        $this->validate->setMallId($mall_id);
        $this->validate->setCodeModel($code);
        $this->validate->hasCode();//是否存在码
        $this->validate->hasExchange();//是否兑换
        $this->validate->hasDisable();//是否禁用
        $this->validate->setLibraryModel($this->validate->codeModel->library_id);
        $this->validate->hasLibrary();//是否存在库
        $this->validate->hasExpireBefore();//是否过期
        $this->validate->hasExpireAfter();//是否到期
    }

    public function cover($mall_id, $code)
    {
        $this->validate->setMallId($mall_id);
        $this->validate->setCodeModel($code);
        $this->validate->hasCode();
        $this->validate->hasExchangeUser();//用户是否合法
        $this->validate->setLibraryModel($this->validate->codeModel->library_id);
        $this->validate->hasLibrary();//是否存在库
    }

    //用户
    public function user($user_id, $has_imitate = false)
    {
        $this->validate->setUser($user_id, $has_imitate);
        $this->validate->hasUser();//是否存在该用户
    }

    public function token($rewards, $token, $type = '')
    {
        $this->validate->hasToken($token);//token是否存在
        $this->validate->hasTokenLegal($rewards, $token, $type);//token是否合法
    }

    public function hasExchangeSetting($setting)
    {
        $this->validate->hasExchangeSetting($setting);//防刷检测
    }

    public function hasImitateUser($extra_info, $has_imitate = false)
    {
        $has_imitate && $this->validate->hasImitateUser($extra_info);
    }

    public function imitateUser($rewards, $mode, $token, $has_imitate = false)
    {
        $has_imitate && $this->validate->hasImitate($rewards, $mode, $token);
    }
}
