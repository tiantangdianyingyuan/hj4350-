<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/12
 * Time: 10:31
 */

namespace app\plugins\pick\forms\common;

use app\forms\common\CommonOptionP;
use app\helpers\PluginHelper;
use app\models\Mall;
use app\models\Model;
use app\plugins\pick\models\PickSetting;

/**
 * @property Mall $mall
 */
class CommonSetting extends Model
{
    public static $setting;

    public function search()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = PickSetting::getList(\Yii::$app->mall->id);

        if (empty($setting)) {
            $setting = $this->getDefault();
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('pick') . '/img/';
        $setting['default_bg_url'] = $iconBaseUrl . 'banner.png';
        $setting['default_form'] =  [
            'text' => [
                'color' => '#353535',
            ],
            'bg' => [
                'color' => '#fff0f0',
            ]
        ];
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'title' => '',
            'rule' => '',
            'bg_url' => PluginHelper::getPluginBaseAssetsUrl('pick') . '/img/banner.png',
            'is_share' => 0,
            'is_territorial_limitation' => 0,
            'send_type' => ['express'],
            'payment_type' => ['online_pay'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'form' =>  [
                'text' => [
                    'color' => '#353535',
                ],
                'bg' => [
                    'color' => '#fff0f0',
                ]
            ]
        ];
    }
}
