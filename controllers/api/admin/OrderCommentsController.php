<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/30
 * Time: 9:58
 */

namespace app\controllers\api\admin;

use app\forms\mall\order_comments\OrderCommentsForm;
use app\forms\mall\order_comments\OrderCommentsReplyForm;

class OrderCommentsController extends AdminController
{
    public function actionIndex()
    {
        $form = new OrderCommentsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->is_reply = \Yii::$app->request->get('is_reply',0);
        $form->comment_type = \Yii::$app->request->get('score',3);
        return $this->asJson($form->search());
    }

    public function actionReply()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderCommentsReplyForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            $form = new OrderCommentsReplyForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        }
    }

    public function actionShow()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->show());
        }
    }

    public function actionUpdateTop() {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTop());
        }
    }
}