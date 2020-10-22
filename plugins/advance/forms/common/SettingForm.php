<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\advance\forms\common;


use app\forms\common\CommonOptionP;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\plugins\advance\models\AdvanceSetting;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public static $setting;

    public function search()
    {
        if (self::$setting) {
            return self::$setting;
        }

        $setting = \app\forms\common\CommonOption::get('advance_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = AdvanceSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
                $setting['is_advance'] = 1;
            }
        }

        $default = $this->getDefault();
        if ($setting) {
            $setting['payment_type'] = $setting['payment_type'] ? json_decode($setting['payment_type'], true) : $default['payment_type'];
            $setting['send_type'] = $setting['send_type'] ? json_decode($setting['send_type'], true) : $default['send_type'];
            $setting['deposit_payment_type'] = $setting['deposit_payment_type'] ? json_decode($setting['deposit_payment_type'], true) : $default['deposit_payment_type'];
            $goodsPoster = json_decode($setting['goods_poster'], true);
            $setting['goods_poster'] = $goodsPoster ?: CommonOption::getPosterDefault();

            $diffSetting = array_diff_key($default, $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = $default;
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

        $res = (new BannerListForm())->search();
        $setting['banner_list'] = $res['data']['list'];

        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_advance' => 1,
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'send_type' => ["express"],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay'],
            'deposit_payment_type' => ['online_pay'],
            'over_time' => 10,
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_full_reduce' => 1,
        ];
    }
}
