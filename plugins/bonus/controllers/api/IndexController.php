<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:23
 */

namespace app\plugins\bonus\controllers\api;

use app\plugins\bonus\forms\api\CaptainForm;
use app\plugins\bonus\forms\api\CashForm;
use app\plugins\bonus\forms\api\IndexForm;
use app\plugins\bonus\forms\mall\MemberForm;
use app\plugins\bonus\forms\mall\SettingForm;

class IndexController extends ApiController
{
    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 申请成为队长
    public function actionApply()
    {
        $form = new CaptainForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->apply());
    }

    //清除申请信息
    public function actionClearApply()
    {
        $form = new CaptainForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->clearApply());
    }

    public function actionApplyStatus()
    {
        $form = new CaptainForm();
        return $this->asJson($form->getStatus());
    }

    public function actionSetting()
    {
        $form = new SettingForm();
        return $this->asJson($form->search());
    }

    // 提现提交申请
    public function actionCash()
    {
        $form = new CashForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionAllMember()
    {
        $form = new MemberForm();
        $res = $form->getAllMember();
        return $this->asJson($res);
    }
}