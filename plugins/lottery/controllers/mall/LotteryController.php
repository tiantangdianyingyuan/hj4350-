<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\controllers\mall;

use app\plugins\Controller;
use app\plugins\lottery\forms\mall\GoodsEditForm;
use app\plugins\lottery\forms\mall\LotteryForm;
use app\plugins\lottery\forms\mall\TemplateForm;
use app\plugins\lottery\jobs\LotteryJob;
use app\plugins\lottery\models\Lottery;

class LotteryController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 手动触发
     * @return array
     */
    public function actionTest()
    {
        $list = new LotteryJob();
        $lottery_id = \Yii::$app->request->post('lottery_id');
        $list->model = Lottery::findOne(intVal($lottery_id));
        $list->execute(1);
        return [
            'code' => 0,
            'msg' => '已触发'
        ];
    }

    public function actionSwitchStatus()
    {
        if (\Yii::$app->request->isPost) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->switchStatus();
        }
    }

    public function actionEditSort()
    {
        if (\Yii::$app->request->isPost) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->editSort();
        }
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->destroy();
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = json_decode($data['form'], true);
                $form->attributes = json_decode($data['form'], true)['detail'];
                $form->attrGroups = json_decode($data['attrGroups'], true);
                return $this->asJson($form->save());
            } else {
                //$form = new GoodsForm();
                //$form->attributes = \Yii::$app->request->get();
                //return $this->asJson($form->search());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionInfo()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->info());
        } else {
            return $this->render('info');
        }
    }

    public function actionGetChild()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getChild());
        }
    }

    public function actionDefault()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->default());
        }
    }

    public function actionSearch()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LotteryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
    }

    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateForm();
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                return $this->asJson($form->getDetail($add, $platform));
            }
            if (\Yii::$app->request->isPost) {
                $form = new TemplateForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
        return $this->render('template');
    }
}
