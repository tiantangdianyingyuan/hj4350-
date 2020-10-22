<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14
 * Time: 11:47
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\share\ShareApplyForm;
use app\forms\api\share\ShareBindForm;
use app\forms\api\share\ShareCashForm;
use app\forms\api\share\ShareCashListForm;
use app\forms\api\share\ShareLevelForm;
use app\forms\api\share\ShareOrderForm;
use app\forms\api\share\ShareTeamForm;
use app\forms\common\share\CommonShareConfig;
use app\forms\common\template\TemplateList;
use app\models\ShareSetting;
use app\forms\api\share\ShareForm;

class ShareController extends ApiController
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
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 自定义设置
    public function actionCustomize()
    {
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->customize());
    }

    //分销佣金
    public function actionBrokerage()
    {
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->brokerage());
    }

    // 申请成为分销商
    public function actionApply()
    {
        $form = new ShareApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 查看分销申请状态--旧版接口
    public function actionApplyStatus()
    {
        $form = new ShareApplyForm();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->getStatus());
    }

    // 查看分销申请状态--新版接口
    public function actionNewApplyStatus()
    {
        $form = new ShareApplyForm();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->getShareStatus());
    }

    // 绑定上级关系
    public function actionBindParent()
    {
        $form = new ShareBindForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    // 获取团队详情
    public function actionTeam()
    {
        $form = new ShareTeamForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 获取分销设置
    public function actionSetting()
    {
        $tpl = ['withdraw_error_tpl', 'withdraw_success_tpl'];
        return $this->asJson([
            'code' => 0,
            'msg' => [
                'config' => CommonShareConfig::config(),
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $tpl),
            ]
        ]);
    }

    // 提现提交申请
    public function actionCash()
    {
        $form = new ShareCashForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 提现明细
    public function actionCashList()
    {
        $form = new ShareCashListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionShareOrder()
    {
        $form = new ShareOrderForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionLevel()
    {
        $form = new ShareLevelForm();
        return $this->asJson($form->getLevelCondition());
    }

    public function actionLevelUp()
    {
        $form = new ShareLevelForm();
        return $this->asJson($form->levelUp());
    }
}
