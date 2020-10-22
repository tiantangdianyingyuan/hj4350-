<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\bonus\controllers\mall;

use app\plugins\bonus\forms\mall\CaptainForm;
use app\plugins\Controller;

class IndexController extends Controller
{
    public function index()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CaptainForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new CaptainForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('index');
    }
}