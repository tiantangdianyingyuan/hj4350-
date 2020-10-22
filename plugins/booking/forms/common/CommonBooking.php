<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\common;

use app\models\Model;
use app\models\Option;
use app\plugins\booking\models\BookingSetting;
use yii\helpers\ArrayHelper;

class CommonBooking extends Model
{
    public static function getSetting()
    {

        $setting = \app\forms\common\CommonOption::get('booking_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = BookingSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }

        if ($setting) {
            $setting['form_data'] = json_decode($setting['form_data'], true) ?: self::getDefault()['form_data'];
            $setting['payment_type'] = json_decode($setting['payment_type'], true) ?: self::getDefault()['payment_type'];
            $setting['goods_poster'] = json_decode($setting['goods_poster'], true) ?: self::getDefault()['goods_poster'];

            $diffSetting = array_diff_key(self::getDefault(), $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = self::getDefault();
        }

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
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

    private static function getDefault()
    {
        return [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_cat' => 1,
            'is_form' => 0,
            'form_data' => [],
            'payment_type' => ['online_pay'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_full_reduce' => 1,
            'is_territorial_limitation' => 0,
        ];
    }
}
