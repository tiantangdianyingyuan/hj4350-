<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 10:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\controllers\api\ApiController;
use app\plugins\bargain\forms\api\ActivityForm;
use app\plugins\bargain\forms\api\BargainOrderListForm;
use app\plugins\bargain\forms\api\BargainResultForm;
use app\plugins\bargain\forms\api\BargainSubmitForm;
use app\plugins\bargain\forms\api\OrderSubmitForm;
use app\plugins\bargain\forms\api\UserJoinBargainForm;
use app\plugins\bargain\forms\api\UserJoinBargainResultForm;

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

    public function actionBargainSubmit()
    {
        $form = new BargainSubmitForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->save());
    }

    public function actionBargainResult()
    {
        $form = new BargainResultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->save());
    }

    public function actionUserJoinBargain()
    {
        $form = new UserJoinBargainForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->save());
    }

    public function actionUserJoinBargainResult()
    {
        $form = new UserJoinBargainResultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionActivity()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionBargainList()
    {
        $form = new BargainOrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionOrderPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->submit());
    }
}
