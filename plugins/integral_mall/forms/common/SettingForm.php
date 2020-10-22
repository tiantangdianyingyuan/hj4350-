<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\common;


use app\forms\common\version\Compatible;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\plugins\integral_mall\models\IntegralMallSetting;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public static $setting;

    public function search()
    {
        $setting = \app\forms\common\CommonOption::get('integral_mall_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = IntegralMallSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }

        if ($setting) {
            $default = $this->getDefault();
            $goodsPoster = json_decode($setting['goods_poster'], true);
            $setting['goods_poster'] = $goodsPoster ?: CommonOption::getPosterDefault();

            $desc = json_decode($setting['desc'], true);
            $setting['desc'] = $desc ?: $default['desc'];

            $setting['payment_type'] = $setting['payment_type'] ?
                \Yii::$app->serializer->decode($setting['payment_type']) :
                $default['payment_type'];
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);

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

        if (!isset($permissionFlip['share'])) {
            $setting['is_share'] = -1;
        }

        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'desc' => [],
            'send_type' => ['express', 'offline'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay'],
            'is_coupon' => 0,
            'rule' => '',
        ];
    }
}
