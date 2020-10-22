<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/1
 * Time: 16:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\mall;


use app\plugins\community\forms\mall\SettingForm;
use app\plugins\Controller;

class SettingController extends Controller
{
    public function actions()
    {
        return [
            'setting-data' => [
                'class' => '\app\plugins\community\components\SettingDataAction'
            ],
            'template' => [
                'class' => '\app\components\TemplateAction',
                'templateClass' => '\app\plugins\community\forms\mall\TemplateForm'
            ]
        ];
    }

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SettingForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('setting');
        }
    }

    public function actionRecruit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SettingForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->recruit());
        } else {
            return $this->render('recruit');
        }
    }
}
