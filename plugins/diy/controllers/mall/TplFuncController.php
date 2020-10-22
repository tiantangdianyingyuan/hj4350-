<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\controllers\mall;

use app\plugins\Controller;
use app\plugins\diy\forms\mall\TplFunc;

class TplFuncController extends Controller
{
    public function actionQuickNavGetMallConfig()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TplFunc();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->quickNavGetMallConfig());
        }
    }
}
