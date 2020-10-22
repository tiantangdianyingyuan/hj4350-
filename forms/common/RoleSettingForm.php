<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common;


use app\models\Model;
use app\models\Option;

class RoleSettingForm extends Model
{
    public $mall_id;

    public function getSetting()
    {
        $setting = CommonOption::get(
            Option::NAME_ROLE_SETTING,
            $this->mall_id ?: \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );
        $setting = $setting ?: [];
        $setting = $this->check($setting, $this->getDefault());

        $arr = ['update_password_status'];
        foreach ($setting as $key => $item) {
            if (in_array($key, $arr)) {
                $setting[$key] = (int)$item;
            }
        }

        return $setting;
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    private function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = $this->check($list[$key], $value);
            }
        }
        return $list;
    }


    protected function getDefault()
    {
        return [
            'logo' => '',
            'copyright' => '',
            'copyright_url' => '',
            'update_password_status' => 1,
        ];
    }
}