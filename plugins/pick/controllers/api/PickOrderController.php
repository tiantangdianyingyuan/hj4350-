<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2020/2/13
 * Time: 9:31
 */

namespace app\plugins\pick\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\pick\forms\api\PickOrderSubmitForm;


class PickOrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionOrderPreview()
    {
        $form = new PickOrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new PickOrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->submit());
    }
}
