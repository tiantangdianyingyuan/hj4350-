<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;

use app\helpers\PluginHelper;
use app\models\Model;
use app\models\Option;

class CommonSetting extends Model
{
    public static $E_KEY = 'exchange_setting';

    public function getDefault()
    {
        $plugin = isset(\Yii::$app->request->hostInfo) ? PluginHelper::getPluginBaseAssetsUrl('exchange') . '/img/' : '';
        $rules = <<<HTML
<p>1、兑换方式：输入兑换码或者扫码兑换。<br/></p>
<p>2、兑换次数：无限制。</p>
<p>3、兑换码有效期：永久有效，随时可兑换。</p>
<p>4、礼包内实物奖励需手动领取，虚拟奖励将自动发放到账户，请注意查收。</p>
<p>
    <img src="{$plugin}rules.png"/>
</p>
HTML;

        return [
            'is_share' => 0,
            'payment_type' => ['online_pay', 'balance'],

            'is_coupon' => 0,
            'svip_status' => 0,
            'is_member_price' => 0,
            'is_integral' => 0,

            'is_rules' => 1,
            'rules' => $rules,

            'is_anti_brush' => 0,
            'anti_brush_minute' => 0,
            'exchange_error' => 0,
            'freeze_hour' => 0,

            'is_limit' => 0,
            'limit_user_num' => 0,

            'is_to_exchange' => 0,
            'is_to_gift' => 0,  // 跳卡

            'limit_user_success_num' => 0,
            'limit_user_type' => 'day',

            'to_exchange_pic' => $plugin . 'to-exchange.png',
            'to_gift_pic' => $plugin . 'to-gift.png',

            'poster' => [],
            'is_full_reduce' => 1,
        ];
    }

    public function get()
    {
        $setting = \app\forms\common\CommonOption::get(
            self::$E_KEY,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        ) ?: [];
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        $setting = array_merge($this->getDefault(), $setting);

        $num = $this->getDefault();
        unset($num['to_exchange_pic']);
        unset($num['to_gift_pic']);
        unset($num['payment_type']);
        unset($num['poster']);
        unset($num['limit_user_type']);
        unset($num['rules']);

        foreach (array_keys($num) as $key => $item) {
            if (isset($setting[$item])) {
                $setting[$item] = intval($setting[$item]);
            }
        }
        return $setting;
    }

    public function set(array $value)
    {
        return \app\forms\common\CommonOption::set(
            self::$E_KEY,
            $value,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );
    }
}
