<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\bonus\controllers\mall;

use app\plugins\bonus\forms\mall\BonusForm;
use app\plugins\bonus\forms\mall\OrderDetailForm;
use app\plugins\bonus\forms\mall\OrderForm;
use app\plugins\Controller;

class OrderController extends Controller
{

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
             $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('detail');
        }
    }

    public function actionRemark()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remark());
    }

    public function actionTeamBonus()
    {
        $form = new BonusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->teamBonus());
    }
}