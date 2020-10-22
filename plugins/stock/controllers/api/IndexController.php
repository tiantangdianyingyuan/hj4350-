<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/4
 * Time: 11:23
 */

namespace app\plugins\stock\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\stock\forms\api\CashForm;
use app\plugins\stock\forms\api\IndexForm;
use app\plugins\stock\forms\api\LevelForm;
use app\plugins\stock\forms\api\StockForm;
use app\plugins\stock\forms\mall\SettingForm;

class IndexController extends ApiController
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
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 申请成为股东
    public function actionApply()
    {
        $form = new StockForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->apply());
    }

    //清除申请信息
    public function actionClearApply()
    {
        $form = new StockForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->clearApply());
    }

    public function actionApplyStatus()
    {
        $form = new StockForm();
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

    //等级页面
    public function actionLevel()
    {
        $form = new LevelForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    //升级
    public function actionLevelUp()
    {
        $form = new LevelForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    //股东可提现，已提现，带打款等信息
    public function actionInfo()
    {
        $form = new StockForm();
        return $this->asJson($form->getInfo());
    }
}
