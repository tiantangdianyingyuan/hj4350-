<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 13:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\components;


use app\core\response\ApiCode;
use app\plugins\community\forms\common\CommonSetting;
use yii\base\Action;

class SettingDataAction extends Action
{
    public function run()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        \Yii::$app->response->data = [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }
}
