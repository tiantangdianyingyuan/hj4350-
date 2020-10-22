<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019年11月19日 11:54:41
 */


namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\FootprintForm;

class FootprintController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new FootprintForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->data());
    }

    public function actionFootprint()
    {
        $form = new FootprintForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->footprint());
    }

    public function actionFootprintDel()
    {
        $form = new FootprintForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->footprintDel());
    }
}
