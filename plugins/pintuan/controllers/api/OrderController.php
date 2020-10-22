<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 16:01
 */


namespace app\plugins\pintuan\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\pintuan\forms\api\OrderForm;
use app\plugins\pintuan\forms\api\OrderSubmitForm;
use app\plugins\pintuan\forms\api\PosterForm;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\Plugin;

class OrderController extends ApiController
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
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->submit());
    }

    // 我的拼团列表
    public function actionList()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }


    // 拼团中的订单
    public function actionPintuanList()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getPintuanList());
    }

    // 拼团中的订单详情
    public function actionPintuanDetail()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getPintuanDetail());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->orderDetailPoster());
    }
}
