<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/3
 * Time: 17:18
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\community\forms\api\AddressForm;
use app\plugins\community\forms\api\ApplyDataForm;
use app\plugins\community\forms\api\ApplyForm;
use app\plugins\community\forms\api\ApplyResultForm;
use app\plugins\community\forms\api\MiddlemanForm;
use app\plugins\community\forms\api\MiddlemanListForm;
use app\plugins\community\forms\api\NoticeForm;
use app\plugins\community\forms\api\ProfitForm;

class MiddlemanController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new ApplyDataForm();
        return $this->asJson($form->getData());
    }

    public function actionApply()
    {
        $form = new ApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->apply());
    }

    public function actionApplyResult()
    {
        $form = new ApplyResultForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getResponseData());
    }

    public function actionApplyPay()
    {
        $form = new ApplyResultForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->applyPay());
    }

    public function actionEditAddress()
    {
        $form = new AddressForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionList()
    {
        $form = new MiddlemanListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionBind()
    {
        $form = new MiddlemanForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->bind());
    }

    public function actionNotice()
    {
        $form = new NoticeForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->notice());
    }

    public function actionProfitList()
    {
        $form = new ProfitForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionProfitDetail()
    {
        $form = new ProfitForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }
}
