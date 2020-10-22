<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 9:21
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonFreeDeliveryRules;
use app\forms\mall\free_delivery_rules\EditForm;
use app\forms\mall\free_delivery_rules\ListForm;
use app\models\FreeDeliveryRules;

class FreeDeliveryRulesController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('index');
    }

    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            $model = FreeDeliveryRules::findOne([
                'id' => $id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0
            ]);
            if (!$model) {
                $model = new FreeDeliveryRules();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
            } else {
                $model->detail = \Yii::$app->serializer->decode($model->detail);
                $model->price = floatval($model->price);
            }
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                $detail = $model->detail;
                if (!isset($detail[0]['condition'])) {
                    $newItem['condition'] = $model->price;
                    $newItem['list'] = $detail;
                    $model->detail = [$newItem];
                }
                unset($model['price']);
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('edit');
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $id = \Yii::$app->request->get('id');
            return CommonFreeDeliveryRules::deleteItem($id);
        }
    }

    public function actionAllList()
    {
        $form = new ListForm();
        return $this->asJson($form->allList());
    }

    // 设置包邮默认
    public function actionStatus($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            return $this->asJson(CommonFreeDeliveryRules::setStatus($id));
        } else {
            throw new \Exception('请求错误');
        }
    }
}
