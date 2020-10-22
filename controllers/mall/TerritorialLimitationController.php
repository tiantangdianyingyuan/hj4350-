<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 14:27
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\mall\territorial_limitation\EditForm;
use app\models\Option;

class TerritorialLimitationController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $model = CommonOption::get(
                    Option::NAME_TERRITORIAL_LIMITATION,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    [
                        'is_enable' => 0
                    ],
                    \Yii::$app->user->identity->mch_id
                );
                $model['is_enable'] = intval($model['is_enable']);
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('index');
    }
}