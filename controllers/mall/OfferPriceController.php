<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 15:30
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\mall\offer_price\EditForm;
use app\models\Option;

class OfferPriceController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $res = CommonOption::get(
                    Option::NAME_OFFER_PRICE,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    [
                        'is_enable' => 0,
                        'total_price' => 0
                    ],
                    \Yii::$app->user->identity->mch_id
                );
                $res['is_enable'] = $res ? intval($res['is_enable']) : 0;
                $res['total_price'] = $res ? floatval($res['total_price']) : 0;
                $res['is_total_price'] = $res && isset($res['is_total_price']) ? floatval($res['is_total_price']) : 1;
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => [
                        'model' => $res
                    ]
                ]);
            }
        }
        return $this->render('index');
    }
}
