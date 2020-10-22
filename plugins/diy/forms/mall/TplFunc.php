<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;

class TplFunc extends Model
{
    private function getConfig()
    {
        $mall = new Mall();
        $config = $mall->getMallSetting()['setting'];
        return [
            'navStyle' => intval($config['quick_navigation_style']),
            'openedPicUrl' => $config['quick_navigation_opened_pic'],
            'closedPicUrl' => $config['quick_navigation_closed_pic'],
            'home' => [
                'opened' => $config['is_quick_home'] == 1,
                'picUrl' => $config['quick_home_pic'],
            ],
            'customerService' => [
                'opened' => $config['is_customer_services'] == 1,
                'picUrl' => $config['customer_services_pic'],
            ],
            'tel' => [
                'opened' => $config['is_dial'] == 1,
                'picUrl' => $config['dial_pic'],
                'number' => $config['contact_tel'],
            ],
            'mApp' => [
                'opened' => $config['is_small_app'] == 1,
                'picUrl' => $config['small_app_pic'],
                'appId' => $config['small_app_id'],
                'page' => $config['small_app_url'],
            ],
            //商城未配置项
            //'web' => [
            //    'opened' => false,
            //    'picUrl' => '',
            //    'url' => '',
            //],
            //'mapNav' => [
            //    'opened' => false,
            //    'picUrl' => '',
            //    'address' => '',
            //    'location' => '',
            //],
            'customize' => [
                'opened' => $config['is_quick_customize'] == 1,
                'picUrl' => $config['quick_customize_pic'],
                'open_type' => $config['quick_customize_open_type'],
                'params' => $config['quick_customize_new_params'],
                'link_url' => $config['quick_customize_link_url'],
                'key' => '',
            ],
        ];
    }

    public function quickNavGetMallConfig()
    {
        try {
            $data = $this->getConfig();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
