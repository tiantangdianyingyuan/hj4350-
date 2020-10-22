<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\validate;

use app\models\User;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\exchange\core\CUser;
use app\plugins\exchange\models\ExchangeCode;

abstract class BasicModel
{
    public $libraryModel;
    public $codeModel;
    public $mall_id;
    public $user;

    //虚拟用户
    public function setUser($user_id, $has_imitate)
    {
        if ($has_imitate) {
            $this->user = (new CUser())->getUser();
        } else {
            $this->user = User::findOne($user_id);
        }
    }

    public function setMallId($mall_id = '')
    {
        empty($mall_id) && $mall_id = \Yii::$app->mall->id;
        $this->mall_id = $mall_id;
    }

    public function setCodeModel($code)
    {
        $this->codeModel = ExchangeCode::find()->where([
            'code' => $code,
            'mall_id' => $this->mall_id,
        ])->one();
    }

    public function setLibraryModel($library_id)
    {
        $this->libraryModel = CommonModel::getLibrary($library_id, $this->mall_id);
    }
}
