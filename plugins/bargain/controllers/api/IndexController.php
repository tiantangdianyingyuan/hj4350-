<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/9
 * Time: 17:06
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\controllers\api;



use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\bargain\forms\api\IndexForm;
use app\plugins\bargain\forms\api\PosterForm;
use app\plugins\bargain\forms\common\BannerListForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['banner']
            ],
        ]);
    }
    public function actionBanner()
    {
        $form = new BannerListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
