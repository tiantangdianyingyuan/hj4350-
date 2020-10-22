<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\common\v2;


use app\models\Model;
use app\models\Option;
use app\plugins\miaosha\models\MiaoshaSetting;
use yii\helpers\ArrayHelper;


class SettingForm extends Model
{
    private static $setting;

    public function search()
    {
        if (self::$setting) {
            return self::$setting;
        }

        $setting = \app\forms\common\CommonOption::get('miaosha_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = MiaoshaSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }

        if ($setting) {
            $goodsPoster = json_decode($setting['goods_poster'], true);
            $setting['goods_poster'] = $goodsPoster ?: CommonOption::getPosterDefault();

            $diffSetting = array_diff_key($this->getDefault(), $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = $this->getDefault();
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

        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'over_time' => 10,
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'goods_poster' => CommonOption::getPosterDefault(),
            'is_territorial_limitation' => 0,
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_full_reduce' => 1,
            'is_offer_price' => 1,
        ];
    }
}
