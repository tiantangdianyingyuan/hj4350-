<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\controllers\mall;

use app\plugins\Controller;
use app\plugins\pintuan\forms\mall\activity\ActivityBatchForm;
use app\plugins\pintuan\forms\mall\activity\ActivityDetailForm;
use app\plugins\pintuan\forms\mall\activity\ActivityEditForm;
use app\plugins\pintuan\forms\mall\activity\ActivityGroupListForm;
use app\plugins\pintuan\forms\mall\activity\ActivityGroupOrderForm;
use app\plugins\pintuan\forms\mall\activity\ActivityListForm;


class ActivityController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ActivityListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ActivityDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $data = \Yii::$app->request->post();
                $dataForm = json_decode($data['form'], true);
                $attrGroups = json_decode($data['attrGroups'], true);
                $form->attributes = isset($dataForm) ? $dataForm : [];
                $form->attrGroups = isset($attrGroups) ? $attrGroups : [];
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionGroups()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityGroupListForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            return $this->render('groups');
        }
    }
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
        } else {
            return $this->render('detail');
        }
    }


    public function actionBatchUpdateHotSell()
    {
        $form = new ActivityBatchForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updateHotSell();

        return $this->asJson($res);
    }

    public function actionBatchUpdateStatus()
    {
        $form = new ActivityBatchForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }

    public function actionBatchDestroy()
    {
        $form = new ActivityBatchForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchDestroy();

        return $this->asJson($res);
    }

    public function actionGroupsOrders() {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityGroupOrderForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();
            return $this->asJson($res);
        } else {
            return $this->render('groups-orders');
        }
    }
}
