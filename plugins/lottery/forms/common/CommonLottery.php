<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\common;

use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\lottery\models\LotterySetting;

class CommonLottery extends Model
{
    public static $setting;

    /**
     * @return LotterySetting|null
     */
    public static function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = LotterySetting::findOne(['mall_id' => \Yii::$app->mall->id]);

        $cs_prompt_default = !isset(\Yii::$app->request->hostInfo) ? '' : PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/prompt.png';
        $default = [
            'type' => 0,
            'title' => '',
            'rule' => '',
            'send_type' => ['express', 'offline'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay'],
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'cs_status' => 0,
            'cs_prompt_pic' => $cs_prompt_default,
            'cs_wechat' => [],
            'cs_wechat_flock_qrcode_pic' => [],
        ];

        if ($setting) {
            $func = function ($key) use ($setting, $default) {
                $data = $setting[$key];
                if ($data && \yii\helpers\BaseJson::decode($data) && is_array(\yii\helpers\BaseJson::decode($data))) {
                    return \yii\helpers\BaseJson::decode($data);
                } else {
                    return $default[$key];
                }
            };
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
            $setting['payment_type'] = $func('payment_type');
            $setting['goods_poster'] = $func('goods_poster');
            $setting['cs_wechat_flock_qrcode_pic'] = $func('cs_wechat_flock_qrcode_pic');
            $setting['cs_wechat'] = $func('cs_wechat');
            $setting['cs_prompt_pic'] = $setting['cs_prompt_pic'] ?: $default['cs_prompt_pic'];
        } else {
            $setting = $default;
        }
        self::$setting = $setting;
        return $setting;
    }
}
