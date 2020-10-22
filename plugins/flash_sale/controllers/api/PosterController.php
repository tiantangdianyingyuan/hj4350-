<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/2
 * Time: 10:40
 */

namespace app\plugins\flash_sale\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\flash_sale\forms\api\poster\PosterNewForm;
use app\plugins\flash_sale\forms\api\poster\PosterConfigForm;

class PosterController extends ApiController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'login' => [
                    'class' => LoginFilter::class,
                    'ignore' => []
                ],
            ]
        );
    }

    public function actionConfig()
    {
        $form = new PosterConfigForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionGenerate()
    {
        $form = new PosterNewForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
