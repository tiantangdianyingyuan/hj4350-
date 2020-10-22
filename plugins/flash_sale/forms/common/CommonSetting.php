<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 15:16
 */

namespace app\plugins\flash_sale\forms\common;

use app\forms\common\CommonOption;
use app\models\Option;
use Yii;
use yii\helpers\ArrayHelper;

class CommonSetting
{
    public function search()
    {
        $setting = CommonOption::get('flash_sale_setting', Yii::$app->mall->id, Option::GROUP_ADMIN);
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        }

        $default = $this->getDefault();
        if ($setting) {
            $diffSetting = array_diff_key($this->getDefault(), $setting);
            $setting = array_merge($setting, $diffSetting);
            $setting = array_map(
                function ($item) {
                    return is_numeric($item) ? (int)$item : $item;
                },
                $setting
            );
        } else {
            $setting = $default;
        }

        $permission = Yii::$app->branch->childPermission(Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        if (!isset($permissionFlip['vip_card'])) {
            $setting['svip_status'] = -1;
        } else {
            $setting['svip_status'] = $setting['svip_status'] == -1 ? 1 : $setting['svip_status'];
        }
        if (!isset($permissionFlip['share'])) {
            $setting['is_share'] = -1;
        } else {
            $setting['is_share'] = $setting['is_share'] == -1 ? 1 : $setting['is_share'];
        }

        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_share' => 0,
            'is_territorial_limitation' => 0,
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_full_reduce' => 1,
            'content' => '',
            'is_offer_price' => 1,
        ];
    }
}
