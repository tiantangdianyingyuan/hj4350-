<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\stock\controllers\mall;

use app\plugins\stock\forms\common\CommonStock;
use app\plugins\stock\forms\mall\BonusForm;
use app\plugins\stock\forms\mall\StockForm;
use app\plugins\stock\forms\mall\QueueStatusForm;
use app\plugins\Controller;

class BonusController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new StockForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new StockForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            }
        }
        return $this->render('index');
    }

    public function actionDetail()
    {
        return $this->render('detail');
    }

    public function actionBonusData()
    {
        $form = new BonusForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search_data());
    }

    public function actionAddBonus()
    {
        $form = new BonusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->is_save = 1;
        return $this->asJson($form->search_data());
    }

    public function actionBonusStatus()
    {
        $form = new QueueStatusForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->status());
    }

    public function actionTest()
    {
        $form = new BonusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->is_save = 1;
        return $this->asJson($form->bonus());
    }
}