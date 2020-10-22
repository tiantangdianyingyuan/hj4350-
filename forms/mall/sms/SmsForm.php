<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\sms;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonSms;
use app\models\Model;

class SmsForm extends Model
{
    public function getDetail()
    {
        $option = CommonAppConfig::getSmsConfig();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
                'setting' => CommonSms::getCommon()->getSetting()
            ]
        ];
    }

    public function getDefault()
    {
        $setting = CommonSms::getCommon()->getSetting();
        $result = [
            'status' => '0',
            'platform' => 'aliyun',// 短信默认支持阿里云
            'mobile_list' => [],
            'access_key_id' => '',
            'access_key_secret' => '',
            'template_name' => '',
        ];
        foreach ($setting as $index => $item) {
            $newItem = [
                'template_id' => ''
            ];
            foreach ($item['variable'] as $value) {
                $newItem[$value['key']] = '';
            }
            $result[$index] = $newItem;
        }
        return $result;
    }
}
