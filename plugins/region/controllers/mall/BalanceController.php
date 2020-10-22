<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/30
 * Time: 16:54
 */

namespace app\plugins\region\controllers\mall;

use app\plugins\Controller;
use app\plugins\region\forms\common\CommonBonus;
use app\plugins\region\forms\mall\BalanceForm;

class BalanceController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BalanceForm();
            $form->attributes = \Yii::$app->request->get();
            $list = $form->search();
            return $this->asJson($list);
        } else {
            return $this->render('index');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CommonBonus();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('detail');
        }
    }

    public function actionAdd()
    {
        return $this->render('add');
    }
}
