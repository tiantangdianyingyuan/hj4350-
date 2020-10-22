<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 15:15
 */

namespace app\controllers\api;


use app\forms\api\CommentsForm;
use app\forms\api\full_reduce\GoodsListForm as FullReduceListForm;
use app\forms\api\goods\AttrForm;
use app\forms\api\goods\GoodsListForm;
use app\forms\api\goods\HotSearchForm;
use app\forms\api\goods\PosterForm;
use app\forms\api\GoodsForm;
use app\forms\api\RecommendForm;

class GoodsController extends ApiController
{
    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionCommentsList()
    {
        $form = new CommentsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    // TODO 即将废弃
    public function actionRecommend()
    {
        $form = new RecommendForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionNewRecommend()
    {
        $form = new RecommendForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getNewList());
    }

    public function actionGoodsList()
    {
        $form = new GoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionFullReduceGoodsList()
    {
        $form = new FullReduceListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionHotSearch()
    {
        $form = new HotSearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionAttr()
    {
        $form = new AttrForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getAttr());
    }
}
