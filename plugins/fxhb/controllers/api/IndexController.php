<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 10:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\controllers\api;



use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\fxhb\forms\api\DetailForm;
use app\plugins\fxhb\forms\api\IndexForm;
use app\plugins\fxhb\forms\api\JoinForm;
use app\plugins\fxhb\forms\api\JoinResultForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        $form->user_activity_id = \Yii::$app->request->get('user_activity_id');
        return $this->asJson($form->search());
    }

    public function actionJoin()
    {
        $form = new JoinForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->join());
    }

    public function actionJoinResult()
    {
        $form = new JoinResultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionDetail()
    {
        $form = new DetailForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionRecommend()
    {
        $form = new IndexForm();
        return $this->asJson($form->getNewList());
    }
}
