<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\TopicForm;

class TopicController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'only' => ['favorite']
            ],
        ]);
    }

    public function actionType()
    {
        $form = new TopicForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->type();
    }
    
    public function actionList()
    {
        $form = new TopicForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    public function actionDetail()
    {
        $form = new TopicForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->detail();
    }

    public function actionFavorite()
    {
        $form = new TopicForm();
        $form->attributes = \Yii::$app->request->post();
        return $form->favorite();
    }
}
