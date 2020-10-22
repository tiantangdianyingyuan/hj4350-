<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\controllers\api;

use app\controllers\api\ApiController;
use app\core\response\ApiCode;
use app\plugins\quick_share\forms\common\CommonQuickShare;

class SettingController extends ApiController
{
    public function actionIndex()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'setting' => CommonQuickShare::getSetting(),
            ]
        ];
    }
}
