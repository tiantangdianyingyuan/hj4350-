<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/1/2
 * Time: 10:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\controllers\mall;


use app\plugins\diy\forms\mall\market\EditForm;
use app\plugins\diy\forms\mall\market\LocalForm;
use app\plugins\diy\forms\mall\market\TemplateForm;
use app\models\User;
use app\plugins\Controller;

class MarketController extends Controller
{
    public function beforeAction($action)
    {
        if (!in_array($action->id, ['get-mall', 'get-template', 'buy', 'pay', 'install', 'issue', 'update-template'])) {
            return parent::beforeAction($action);
        }
        /* @var User $user */
        $user = \Yii::$app->user->identity;
        if ($user->identity->is_super_admin != 1) {
            throw new \Exception('用户无权限请求');
        }
        return parent::beforeAction($action);
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    public function actionLoading()
    {
        $form = new LocalForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->loadTemplate());
    }

    public function actionGetMall()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getMall());
    }

    public function actionGetTemplate()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getTemplate());
    }

    public function actionIssue()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->issue());
    }

    public function actionBuy()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->buy());
    }

    public function actionPay()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->pay());
    }

    public function actionInstall()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->install());
    }

    public function actionUpdateTemplate()
    {
        $form = new EditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->update());
    }
}
