<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\stock\controllers\mall;

use app\plugins\stock\forms\mall\CashApplyForm;
use app\plugins\stock\forms\mall\CashListForm;
use app\plugins\Controller;

class CashController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new CashListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new CashListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            }
        }
        return $this->render('index');
    }

    public function actionCashApply()
    {
        $form = new CashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionRemark()
    {
        $form = new CashApplyForm();
        $form->id = \Yii::$app->request->post('id');
        $form->content = \Yii::$app->request->post('remark');
        return $this->asJson($form->remark());
    }

    public function actionDetail()
    {
        return $this->render('detail');
    }
}