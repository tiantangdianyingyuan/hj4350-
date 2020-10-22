<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\controllers\mall;

use app\plugins\community\forms\mall\ActivityDetailForm;
use app\plugins\community\forms\mall\ActivityLockingForm;
use app\plugins\community\forms\mall\ImportGoodsForm;
use app\plugins\community\forms\mall\RefundListForm;
use app\plugins\Controller;
use app\plugins\community\forms\mall\ActivityEditForm;
use app\plugins\community\forms\mall\ActivityForm;
use app\plugins\community\forms\mall\ActivityGoodsEditForm;
use app\plugins\community\forms\mall\GoodsEditForm;

class ActivityController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new ActivityForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new ActivityDetailForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->post();
                $form->getDetail();
                return false;
            } else {
                return $this->render('detail');
            }
        }
    }

    public function actionOrderGoods()
    {
        $form = new ActivityDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getOrderGoods());
    }

    public function actionCheck()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ActivityForm();
                $form->attributes = \Yii::$app->request->get();
                $form->scenario = 'check';
                return $this->asJson($form->check());
            }
        }
    }

    public function actionGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getGoods());
        }
    }

    public function actionEditGoods()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ActivityGoodsEditForm();
                $form->scenario = 'activity';
                $form->attributes = \Yii::$app->request->post();
                $form->community = json_decode($form->community, true);
                return $this->asJson($form->editGoods());
            }
        }
    }

    public function actionEditActivityGoods()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ActivityGoodsEditForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getGoods());
            } else {
                $data = \Yii::$app->request->post();
                $form = new GoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attrGroups = json_decode($data['attrGroups'], true);
                $res = $form->save();
                return $this->asJson($res);
            }
        } else {
            return $this->render('edit-activity-goods');
        }
    }

    public function actionEditStatus()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->status());
        }
    }

    public function actionMallGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getMallGoods());
        }
    }

    public function actionRefundList()
    {
        $form = new RefundListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionRefund()
    {
        $form = new RefundListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->refund());
    }

    public function actionLocking()
    {
        $form = new ActivityLockingForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    public function actionImportGoods()
    {
        $form = new ImportGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->import());
    }
}
