<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/11
 * Time: 11:50
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\mall;


use app\plugins\community\forms\mall\OrderListForm;
use app\plugins\Controller;

class OrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new OrderListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->post();
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }
}
