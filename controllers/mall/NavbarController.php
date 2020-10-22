<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\forms\mall\navbar\NavbarEditForm;
use app\forms\mall\navbar\NavbarForm;

class NavbarController extends MallController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new NavbarForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new NavbarEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        }

        return $this->render('setting');
    }

    /**
     * 恢复默认
     * @return \yii\web\Response
     */
    public function actionDefault()
    {
        $form = new NavbarForm();
        $res = $form->restoreDefault();

        return $this->asJson($res);
    }
}
