<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/15
 * Time: 10:00
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\controllers\mall;


use app\forms\mall\theme_color\ThemeColorForm;

class ThemeColorController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ThemeColorForm();
            if (\Yii::$app->request->isGet) {
                return $this->asJson($form->getList());
            } else {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
        return $this->render('index');
    }
}
