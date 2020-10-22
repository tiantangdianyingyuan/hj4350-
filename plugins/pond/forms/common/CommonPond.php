<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\forms\common;

use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\pond\models\PondSetting;

class CommonPond extends Model
{
    public static $setting;
    public static function getNewName($item, $status = 'start')
    {
        switch ($item['type']) {
            case 1:
                return $item['name'] ?: $item['price'] . '元红包';
            case 2:
                if ($status == 'start') {
                    return $item['coupon']['name'];
                } else {
                    return \Yii::$app->serializer->decode($item['coupon']['coupon_data'])->name;
                }
            case 3:
                return $item['name'] ?: $item['num'] . '积分';
            case 4:
                return $item['goods']['goodsWarehouse']['name'];
            case 5:
                return '谢谢参与';
            default:
                return '';
        }
    }

    /**
     * @return PondSetting|null
     */
    public static function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = PondSetting::findOne(['mall_id' => \Yii::$app->mall->id]);
        if ($setting) {
            if ($setting->payment_type) {
                $setting->payment_type = \Yii::$app->serializer->decode($setting->payment_type);
            } else {
                $setting->payment_type = ['online_pay'];
            }
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);

            if (isset(\Yii::$app->request->hostInfo)) {
                empty($setting['bg_pic']) && $setting['bg_pic'] = PluginHelper::getPluginBaseAssetsUrl('pond') . '/img/pond-head.png';
                empty($setting['bg_color']) && $setting['bg_color'] = '#f12416';
                empty($setting['bg_color_type']) && $setting['bg_color_type'] = 'pure';
                empty($setting['bg_gradient_color']) && $setting['bg_gradient_color'] = '#f12416';
            }
        }
        //防事务
        self::$setting = $setting;
        return $setting;
    }
}
