<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 9:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\common;


use app\forms\common\CommonOption;
use app\helpers\PluginHelper;
use app\models\Mall;
use app\plugins\community\forms\Model;

/**
 * Class CommonSetting
 * @package app\plugins\community\forms\common
 * @property Mall $mall
 */
class CommonSetting extends Model
{
    /* @var self $instance */
    public static $instance;
    public $mall;

    /**
     * @param null $mall
     * @return CommonSetting
     */
    public static function getCommon($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (self::$instance && self::$instance->mall->id == $mall->id) {
            return self::$instance;
        }
        self::$instance = new self();
        self::$instance->mall = $mall;
        return self::$instance;
    }

    const POSTER_STYLE = ['1', '2', '3', '4'];
    const IMAGE_STYLE = ['1', '2', '3', '4', '5'];
    const ACTIVITY_POSTER_STYLE = ['1', '2', '3', '4'];

    public function getDefault()
    {
        try {
            $iconUrl = PluginHelper::getPluginBaseAssetsUrl('community') . '/img';
        } catch (\Exception $exception) {
            $iconUrl = '';
        }
        return [
            'is_apply' => '1', // 是否开启团长审核
            'is_apply_money' => '0', // 是否开启申请门槛
            'is_allow_change' => '0', // 是否允许更换团长
            'apply_money' => 0, // 门槛金额
            'apply_money_name' => '产品使用费', // 门槛名称
            'middleman' => '团长', // 社区团购团长名称
            'sell_out_sort' => '1', // 售罄显示方式: 1--显示 2--隐藏 3--显示且自动排在最后
            'app_share_title' => '',
            'app_share_pic' => '',
            'banner' => $iconUrl . '/banner.png',
            'recruit_title' => '招募令',
            'recruit_content' => "<p><img src=\"{$iconUrl}/recruit.png\"/></p>",
            'poster_style' => self::POSTER_STYLE,
            'image_style' => self::IMAGE_STYLE,
            'goods_sign_pic' => $iconUrl . '/goods-sign-pic.png',
            'activity_poster_style' => self::ACTIVITY_POSTER_STYLE,
            'image_bg' => $iconUrl . '/activity-bg.png',
            'is_share' => 1,
            'pay_type' => ['auto'], // 提现方式
            'min_money' => 0, // 提现门槛
            'cash_service_charge' => 0, // 提现手续费
            'free_cash_min' => 0, // 免提现手续费门槛
        ];
    }

    public static $setting;

    public function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $default = $this->getDefault();
        $default['default_banner'] = $default['banner'];
        $default['default_image_bg'] = $default['image_bg'];
        $default['default_goods_sign_pic'] = $default['goods_sign_pic'];
        $option = CommonOption::get('community_setting', $this->mall->id, 'plugin', $default);
        self::$setting = $this->checkDefault($default, $option);
        self::$setting['is_share'] = intval(self::$setting['is_share']);
        sort(self::$setting['poster_style']);
        sort(self::$setting['image_style']);
        sort(self::$setting['activity_poster_style']);
        return self::$setting;
    }

    /**
     * @param $default
     * @param $option
     * @return mixed
     *
     */
    public function checkDefault($default, $option)
    {
        foreach ($default as $index => $value) {
            if (isset($option[$index])) {
                $default[$index] = $option[$index];
            }
        }
        return $default;
    }
}
