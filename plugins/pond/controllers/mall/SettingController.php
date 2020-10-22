<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\controllers\mall;

use app\plugins\Controller;
use app\plugins\pond\forms\mall\PondSettingForm;

class SettingController extends Controller
{
    public function batchGoods()
    {
        $goods = \app\models\Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'sign' => 'pond',
            'is_delete' => 0,
            'status' => 0
        ])->asArray()->all();
        foreach ($goods as $item) {
            $item->status = 1;
            $item->save();
        }
        dd('OK');
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PondSettingForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }
}
