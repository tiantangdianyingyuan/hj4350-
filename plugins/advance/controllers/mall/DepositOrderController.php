<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/27
 * Time: 10:58
 */

namespace app\plugins\advance\controllers\mall;

use app\plugins\advance\forms\mall\DepositOrderForm;
use app\plugins\Controller;

class DepositOrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
            } else {
                $form = new DepositOrderForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new DepositOrderForm();
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
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
            } else {
                $form = new DepositOrderForm();
                $form->scenario = 'detail';
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('detail');
        }
    }

    public function actionRemark()
    {
        if (\Yii::$app->request->isPost) {
            $form = new DepositOrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->remark());
        }
    }

    /**
     * 强制取消
     */
    public function actionCancel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new DepositOrderForm();
            $form->scenario = 'cancel';
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->cancel());
        }
    }

    public function actionDel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new DepositOrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->del());
        }
    }
}