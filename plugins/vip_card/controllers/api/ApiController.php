<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 14:12
 */

namespace app\plugins\vip_card\controllers\api;

use app\core\response\ApiCode;
use app\plugins\vip_card\models\VipCardSetting;

class ApiController extends \app\controllers\api\ApiController
{
    public function beforeAction($action)
    {
        //权限判断
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            \Yii::$app->response->data = ['code' => ApiCode::CODE_ERROR, 'msg' => '无超级会员卡权限'];
            return false;
        }
        //判断会员卡开关
        $model = VipCardSetting::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (empty($model) || $model->is_vip_card == 0) {
            \Yii::$app->response->data = ['code' => ApiCode::CODE_ERROR, 'msg' => '超级会员卡已关闭'];
            return false;
        }
        return parent::beforeAction($action);
    }
}