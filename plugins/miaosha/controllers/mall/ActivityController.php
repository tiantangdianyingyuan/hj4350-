<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\controllers\mall;


use app\forms\common\convert\TestQueueJob;
use app\jobs\TestQueueServiceJob;
use app\plugins\Controller;
use app\plugins\miaosha\forms\mall\activity\ActivityEditForm;
use app\plugins\miaosha\forms\mall\activity\ActivityForm;
use app\plugins\miaosha\forms\mall\activity\ActivityListForm;

class ActivityController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->get('search');
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = json_decode($data['form'], true);
                $form->attributes = json_decode($data['form'], true)['detail'];
                $form->attrGroups = json_decode($data['attrGroups'], true);
                $res = $form->save();

                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
        } else {
            return $this->render('detail');
        }
    }

    public function actionData()
    {
        if (\Yii::$app->request->isAjax) {
        } else {
            return $this->render('data');
        }
    }

    // 批量更新活动状态
    public function actionBatchUpdateStatus()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }

    // 批量删除
    public function actionBatchDestroy()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchDestroy();

        return $this->asJson($res);
    }

    public function actionActivityGoods()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->activityGoods();

        return $this->asJson($res);
    }
}