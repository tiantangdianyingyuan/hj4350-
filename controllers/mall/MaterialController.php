<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\mall;


class MaterialController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {

        } else {
            return $this->render('index');
        }
    }
}