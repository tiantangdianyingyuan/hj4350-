<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\scratch\forms\common;

use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\scratch\models\ScratchSetting;

class CommonScratch extends Model
{
    public static $setting;

    public static function getNewName($item, $status = 'start')
    {
        switch ($item['type']) {
            case 1:
                return $item['price'] . '元红包';
                break;
            case 2:
                if ($status == 'start') {
                    return $item['coupon']['name'];
                } else {
                    return \Yii::$app->serializer->decode($item['coupon']['coupon_data'])->name;
                }
                break;
            case 3:
                return $item['num'] . '积分';
                break;
            case 4:
                return $item['goods']['goodsWarehouse']['name'];
                break;
            case 5:
                return '谢谢参与';
                break;
            default:
                return '';
        }
    }


    /**
     * @return ScratchSetting|null
     */
    public static function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = ScratchSetting::findOne(['mall_id' => \Yii::$app->mall->id]);
        if ($setting) {
            if ($setting->payment_type) {
                $setting->payment_type = \Yii::$app->serializer->decode($setting->payment_type);
            } else {
                $setting->payment_type = ['online_pay'];
            }
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
            //防事务
            if (isset(\Yii::$app->request->hostInfo)) {
                empty($setting['bg_pic']) && $setting['bg_pic'] = PluginHelper::getPluginBaseAssetsUrl('scratch') . '/img/scratch-bg.png';
            }
        }
        self::$setting = $setting;
        return $setting;
    }
}
