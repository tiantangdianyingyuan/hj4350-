<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/15
 * Time: 11:01
 */

namespace app\plugins\flash_sale\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\flash_sale\forms\api\OrderSubmitForm;
use Yii;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'login' => [
                    'class' => LoginFilter::class,
                ],
            ]
        );
    }

    public function actionOrderPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = Yii::$app->serializer->decode(Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = Yii::$app->serializer->decode(Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->submit());
    }
}
