<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\api\filters;


use app\core\response\ApiCode;
use app\models\AdminInfo;
use yii\base\ActionFilter;

class MallDisabledFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        if (\Yii::$app->mall->is_disable) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_STORE_DISABLED,
                'msg' => '商城被禁用。',
                'data' => [
                    'text' => '该小程序已被禁用'
                ]
            ];
            return false;
        }

        $adminInfo = AdminInfo::findOne(['user_id' => \Yii::$app->mall->user_id]);
        if (!$adminInfo || (\Yii::$app->mall->expired_at != '0000-00-00 00:00:00' && strtotime(\Yii::$app->mall->expired_at) < time())
        || ($adminInfo->expired_at != '0000-00-00 00:00:00' && strtotime($adminInfo->expired_at) < time())) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_STORE_DISABLED,
                'msg' => '商城已过期。',
                'data' => [
                    'text' => '该小程序已过期'
                ]
            ];
            return false;
        }

        return true;
    }
}
