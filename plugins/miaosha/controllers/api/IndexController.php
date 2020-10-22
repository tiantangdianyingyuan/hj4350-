<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\response\ApiCode;
use app\plugins\miaosha\forms\api\CartEditForm;
use app\plugins\miaosha\forms\api\IndexForm;
use app\plugins\miaosha\forms\api\MsPosterForm;
use app\plugins\miaosha\forms\common\BannerListForm;
use app\plugins\miaosha\forms\common\SettingForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index']
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new BannerListForm();
        $form->attributes = \Yii::$app->request->get();

        $data = [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'banners' => $form->search(),
                'setting' => (new SettingForm())->search(),
            ]
        ];
        return $this->asJson($data);
    }

    public function actionAddCart()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->addCart());
    }

    public function actionCartEdit()
    {
        $form = new CartEditForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->save());
    }

    public function actionPoster()
    {
        $form = new MsPosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
