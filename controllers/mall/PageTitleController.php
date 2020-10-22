<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\forms\mall\page_title\PageTitleEditForm;
use app\forms\mall\page_title\PageTitleForm;

class PageTitleController extends MallController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PageTitleForm();
                $res = $form->getList();

                return $this->asJson($res);
            } else {
                $form = new PageTitleEditForm();
                $form->data = \Yii::$app->request->post('list');

                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    /**
     * 恢复默认
     * @return \yii\web\Response
     */
    public function actionRestoreDefault()
    {
        $form = new PageTitleForm();
        $res = $form->restoreDefault();

        return $this->asJson($res);
    }
}
