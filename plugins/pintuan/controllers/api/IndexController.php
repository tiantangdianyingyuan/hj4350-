<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\response\ApiCode;
use app\plugins\pintuan\forms\api\PosterForm;
use app\plugins\pintuan\forms\common\BannerListForm;
use app\plugins\pintuan\forms\common\SettingForm;

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
    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
