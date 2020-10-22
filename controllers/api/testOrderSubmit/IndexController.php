<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/28
 * Time: 17:08
 */

namespace app\controllers\api\testOrderSubmit;


use app\controllers\api\ApiController;

class IndexController extends ApiController
{

    public function actionPreview()
    {
        $form = new TestOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableMemberPrice(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setSign('test')
            ->preview());
    }

    public function actionSubmit()
    {
        $form = new TestOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableMemberPrice(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setSign('test')
            ->submit());
    }
}