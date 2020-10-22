<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/8
 * Time: 15:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\community\forms\api\cart\CartDeleteForm;
use app\plugins\community\forms\api\cart\CartEditForm;
use app\plugins\community\forms\api\cart\CartForm;
use app\plugins\community\forms\api\cart\CartListForm;
use app\plugins\community\forms\api\cart\CartResultForm;

class CartController extends ApiController
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
        $form = new CartListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionAdd()
    {
        $form = new CartForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->job());
    }

    public function actionAddResult()
    {
        $form = new CartResultForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }

    public function actionDelete()
    {
        $form = new CartDeleteForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    public function actionEdit()
    {
        $form = new CartEditForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->job());
    }
}
