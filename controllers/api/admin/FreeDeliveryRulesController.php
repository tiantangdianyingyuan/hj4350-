<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/9/23
 * Time: 15:40
 */

namespace app\controllers\api\admin;

use app\forms\mall\free_delivery_rules\ListForm;

class FreeDeliveryRulesController extends AdminController
{
    public function actionAllList()
    {
        $form = new ListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->allList());
    }
}
