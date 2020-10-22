<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 16:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\dianqilai\controllers\api;


use app\controllers\Controller;
use app\plugins\dianqilai\forms\CallbackForm;

class IndexController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionIndex()
    {
        $form = new CallbackForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }
}
