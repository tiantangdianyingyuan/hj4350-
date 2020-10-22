<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/7
 * Time: 11:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\controllers\api\admin\v2;



use app\controllers\api\admin\AdminController;
use app\forms\api\admin\v2\ReviewForm;

class ReviewController extends AdminController
{
    public function actionIndex()
    {
        $form = new ReviewForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionTabs()
    {
        $form = new ReviewForm();
        return $this->asJson($form->getTabs());
    }

    public function actionSwitchStatus()
    {
        $form = new ReviewForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->switchStatus());
    }

    public function actionDetail()
    {
        $form = new ReviewForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }
}
