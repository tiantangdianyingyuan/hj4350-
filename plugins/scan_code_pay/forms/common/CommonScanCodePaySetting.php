<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\common;


use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\scan_code_pay\models\ScanCodePaySetting;
use app\plugins\scan_code_pay\Plugin;
use yii\helpers\ArrayHelper;

class CommonScanCodePaySetting extends Model
{
    public function getSetting()
    {
        $setting = ScanCodePaySetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->one();
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
            $setting['payment_type'] = json_decode($setting['payment_type']) ?: $this->getDefault()['payment_type'];
            $setting['poster'] = \Yii::$app->serializer->decode($setting['poster']);
        } else {
            $setting = $this->getDefault();
        }

        $arr = ['is_show', 'size', 'top', 'left', 'type'];
        foreach ($setting['poster'] as $key1 => $item) {
            foreach ($item as $key2 => $item2) {
                if (in_array($key2, $arr)) {
                    $setting['poster'][$key1][$key2] = (int)$item2;
                }
            }
        }

        return $setting;
    }

    public function getDefault()
    {
        $pluginName = (new Plugin())->getName();
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($pluginName) . '/img';
        return [
            'is_scan_code_pay' => 0,
            'payment_type' => ['online_pay'],
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'share_type' => 1,
            'share_commission_first' => 0,
            'share_commission_second' => 0,
            'share_commission_third' => 0,
            'poster' => [
                'bg_pic' => [
                    'url' => $imageBaseUrl . '/poster_bg.png',
                    'is_show' => 1
                ],
                'qr_code' => [
                    'is_show' => 1,
                    'size' => 120,
                    'top' => 265,
                    'left' => 115,
                    'type' => 1,
                    'file_type' => 'image',
                ],
            ],
        ];
    }
}