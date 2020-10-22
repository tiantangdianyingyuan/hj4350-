<?php

namespace app\controllers\api;

use app\forms\api\AddressForm;
use app\forms\api\article\ArticleForm;
use app\forms\api\article\ArticleListForm;
use app\forms\api\cat\CatsForm;
use app\forms\api\GoodsListForm;
use app\forms\api\QrCodeForm;
use app\forms\api\SearchForm;
use app\forms\common\CommonBuyPrompt;
use app\forms\common\CommonDistrict;

class DefaultController extends ApiController
{
    public function actionDistrict()
    {
        $commonDistrict = new CommonDistrict();
        $district = $commonDistrict->search();
        return [
            'code' => 0,
            'data' => [
                'list' => $district
            ]
        ];
    }

    public function actionCatsList()
    {
        $form = new CatsForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->searchTemp();
    }

    public function actionGoodsList()
    {
        $form = new GoodsListForm();
        $form->cat_id = \Yii::$app->request->get('cat_id');
        $form->sort = \Yii::$app->request->get('sort');
        $form->sort_type = \Yii::$app->request->get('sort_type');
        $form->keyword = \Yii::$app->request->get('keyword');
        $form->page = \Yii::$app->request->get('page');
        $form->setSign(\Yii::$app->request->get('sign'));
        $form->coupon_id = \Yii::$app->request->get('coupon_id');
        $form->mch_id = \Yii::$app->request->get('mch_id');
        return $form->search();
    }

    public function actionArticleList()
    {
        $form = new ArticleListForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    public function actionArticle()
    {
        $form = new ArticleForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    public function actionBuyPrompt()
    {
        return $this->asJson(CommonBuyPrompt::get());
    }

    public function actionQrCodeParameter()
    {
        $form = new QrCodeForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getParameter());
    }

    public function actionSearchList()
    {
        $form = new SearchForm();
        return $this->asJson($form->getSearch());
    }

    public function actionAutoAddressInfo()
    {
        $form = new AddressForm();
        return $this->asJson($form->autoAddressInfo());
    }
}
