<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\response\ApiCode;
use app\forms\common\CommonUser;
use app\plugins\integral_mall\forms\api\PosterForm;
use app\plugins\integral_mall\forms\common\BannerListForm;
use app\plugins\integral_mall\forms\common\SettingForm;

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
                'userInfo' => CommonUser::getUserInfo('integral'),
            ]
        ];
        return $this->asJson($data);
    }

    //海报
    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

}
