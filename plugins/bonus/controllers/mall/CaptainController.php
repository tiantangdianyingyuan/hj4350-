<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\bonus\controllers\mall;

use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\forms\mall\CaptainForm;
use app\plugins\bonus\forms\mall\QueueStatusForm;
use app\plugins\Controller;

class CaptainController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new CaptainForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new CaptainForm();
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

    /**队长申请审核**/
    public function actionApply()
    {
        $form = new CommonCaptain();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->become());
    }

    public function actionApplyStatus()
    {
        $form = new QueueStatusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->type = 'applyStatus';
        return $this->asJson($form->status());
    }

    /**解除队长**/
    public function actionRemove()
    {
        $form = new CommonCaptain();
        $form->user_id = \Yii::$app->request->post('user_id');
        $form->reason = \Yii::$app->request->post('reason');
        return $this->asJson($form->remove());
    }

    public function actionRemoveStatus()
    {
        $form = new QueueStatusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->type = 'removeStatus';
        return $this->asJson($form->status());
    }

    /**添加队长备注**/
    public function actionRemark()
    {
        $form = new CaptainForm();
        $form->scenario = 'remark';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remark());
    }

    /**调整队长等级**/
    public function actionLevel()
    {
        $form = new CaptainForm();
        $form->scenario = 'level';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->level());
    }

    /**删除被拒绝的队长申请**/
    public function actionDelete()
    {
        $form = new CaptainForm();
        $form->scenario = 'delete';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}