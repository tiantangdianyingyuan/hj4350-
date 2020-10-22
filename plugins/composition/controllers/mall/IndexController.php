<?php
/**
 * @copyright  ©2020 浙江禾匠信息科技
 * @author 风哀伤
 * @link http://www.zjhejiang.com/
 * Date Time: 2020/02/11 14:42
 */

namespace app\plugins\composition\controllers\mall;

use app\plugins\composition\forms\mall\GoodsForm;
use app\plugins\composition\forms\mall\ListForm;
use app\plugins\composition\forms\mall\SettingForm;
use app\plugins\composition\forms\mall\UpdateForm;
use app\plugins\Controller;

class IndexController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SettingForm();
            if (\Yii::$app->request->isGet) {
                return $this->asJson($form->getSetting());
            } else {
                $ruleForm = \Yii::$app->request->post('ruleForm');
                $form->attributes = json_decode($ruleForm, true);
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
        return $this->render('list');
    }

    public function actionFixed()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsForm();
            if (\Yii::$app->request->isPost) {
                $ruleForm = \Yii::$app->request->post('form');
                $ruleForm = json_decode($ruleForm, true);
                $ruleForm['type'] = 1;
                $form->attributes = $ruleForm;
                return $this->asJson($form->save());
            } else {
                $form->id = \Yii::$app->request->get('id');
                $form->type = 1;
                return $this->asJson($form->getOne());
            }
        }
        return $this->render('fixed');
    }

    public function actionGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsForm();
            if (\Yii::$app->request->isPost) {
                $ruleForm = \Yii::$app->request->post('form');
                $ruleForm = json_decode($ruleForm, true);
                $ruleForm['type'] = 2;
                $form->attributes = $ruleForm;
                return $this->asJson($form->save());
            } else {
                $form->id = \Yii::$app->request->get('id');
                $form->type = 2;
                return $this->asJson($form->getOne());
            }
        }
        return $this->render('goods');
    }

    public function actionGetGoods()
    {
        $form = new GoodsForm();
        $search = json_decode(\Yii::$app->request->get('search'), true);
        $form->type = \Yii::$app->request->get('type');
        $form->hostId = \Yii::$app->request->get('host_id');
        return $this->asJson($form->getGoods($search['keyword']));
    }

    public function actionUpdate()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UpdateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->update());
        }
    }

    public function actionBatchUpdate()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UpdateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batch());
        }
    }
}
