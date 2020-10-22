<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 17:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\api;


class IndexController extends ApiController
{
    public function actions()
    {
        return [
            'setting-data' => [
                'class' => '\app\plugins\community\components\SettingDataAction'
            ]
        ];
    }
}
