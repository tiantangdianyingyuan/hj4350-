<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 10:37
 */

namespace app\forms\common;


use app\core\response\ApiCode;
use app\models\PostageRules;
use Yii;

class CommonPostageRules
{
    // 设置默认运费规则(一个商城仅有一个默认运费规则)
    public static function setStatus($id = null)
    {
        $model = PostageRules::findOne([
            'id' => $id,
            'is_delete' => 0,
            'mall_id' => Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '运费规则不存在'
            ];
        } else {
            PostageRules::updateAll(['status' => 0], [
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

    // 删除单个运费规则
    public static function deleteItem($id = null)
    {
        $model = PostageRules::findOne([
            'id' => $id,
            'is_delete' => 0,
            'mall_id' => Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '没有可删除的选项'
            ];
        } else {
            $model->is_delete = 1;
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }

    // 删除所有运费规则
    public static function deleteItemAll()
    {
        $count = PostageRules::updateAll(['is_delete' => 1], [
            'is_delete' => 0,
            'mall_id' => Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if ($count > 0) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "删除成功，共删除{$count}个"
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '没有可删除的选项'
            ];
        }
    }
}