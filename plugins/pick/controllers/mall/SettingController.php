<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/12
 * Time: 9:31
 */

namespace app\plugins\pick\controllers\mall;

use app\plugins\Controller;
use app\plugins\pick\forms\mall\SettingForm;

class SettingController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new SettingForm();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('index');
        }
    }
}
