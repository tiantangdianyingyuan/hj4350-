<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 10:39
 */

namespace app\forms\common;

use app\core\response\ApiCode;
use app\models\FreeDeliveryRules;
use Yii;

class CommonFreeDeliveryRules
{
    public static function deleteItem($id = null)
    {
        $model = FreeDeliveryRules::findOne([
            'id' => $id,
            'mall_id' => Yii::$app->mall->id,
            'mch_id' => Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '没有可删除的选项'
            ];
        } else {
            $model->is_delete = 1;
            if ($model->save()) {
                return [
                    'code' => 0,
                    'msg' => '删除成功'
                ];
            } else {
                return [
                    'code' => 1,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }

    // 设置默认包邮规则(一个商城仅有一个默认运费规则)
    public static function setStatus($id = null)
    {
        $model = FreeDeliveryRules::findOne([
           'id' => $id,
           'is_delete' => 0,
           'mall_id' => Yii::$app->mall->id,
           'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '包邮规则不存在'
            ];
        } else {
            FreeDeliveryRules::updateAll(['status' => 0], [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ]);
            $model->status = 1;
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功'
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }
}
