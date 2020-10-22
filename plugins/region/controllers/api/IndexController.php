<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/4
 * Time: 11:23
 */

namespace app\plugins\region\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\region\forms\api\CashForm;
use app\plugins\region\forms\api\IndexForm;
use app\plugins\region\forms\api\LevelForm;
use app\plugins\region\forms\api\RegionForm;
use app\plugins\region\forms\mall\SettingForm;

class IndexController extends ApiController
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

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 申请成为代理
    public function actionApply()
    {
        $form = new RegionForm();
        $form->attributes = \Yii::$app->request->post();
        $form->city_id = json_decode(\Yii::$app->request->post('city_id'), true);
        $form->district_id = json_decode(\Yii::$app->request->post('district_id'), true);
        return $this->asJson($form->apply());
    }

    //清除申请信息
    public function actionClearApply()
    {
        $form = new RegionForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->clearApply());
    }

    //清除升级信息
    public function actionClearLevelUp()
    {
        $form = new RegionForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->clearLevelUp());
    }

    public function actionApplyStatus()
    {
        $form = new RegionForm();
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

    //升级
    public function actionLevelUp()
    {
        $form = new LevelForm();
        $form->attributes = \Yii::$app->request->post();
        $form->city_id = json_decode(\Yii::$app->request->post('city_id'), true);
        return $this->asJson($form->save());
    }

    //区域可提现，已提现，带打款等信息
    public function actionInfo()
    {
        $form = new RegionForm();
        return $this->asJson($form->getInfo());
    }
}
