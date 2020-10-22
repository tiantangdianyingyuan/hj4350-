<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020-02-11
 * Time: 16:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common;


use app\forms\common\CommonOption;
use app\helpers\PluginHelper;
use app\models\Model;

class CommonSetting extends Model
{
    public static $instance;

    public $mall;

    public static function getCommon($mall = null)
    {
        if (self::$instance) {
            return self::$instance;
        }
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        self::$instance = new self();
        self::$instance->mall = $mall;
        return self::$instance;
    }

    public function getDefault()
    {
        try {
            $iconUrl = PluginHelper::getPluginBaseAssetsUrl('composition') . '/img';
        } catch (\Exception $exception) {
            $iconUrl = '';
        }
        return [
            'is_share' => 1,
            'payment_type' => ['online_pay'],
            'send_type' => ['express', 'offline'],
            'is_coupon' => 0,
            'title' => '套餐组合购买规则',
            'rule' => '1.套餐组合分固定套餐和搭配套餐两种类型
2.不可对固定套餐进行拆分，需整体购买固定套餐
3.搭配套餐中的主商品为必须购买商品，且需要和至少一件
搭配商品进行组合购买
4.仅支持套餐单独下单，不可添加到购物车与其他商品同时
下单
5.不支持使用会员价折扣、超级会员卡折扣和积分抵扣，但可
使用优惠券',
            'activityBg' => $iconUrl . '/banner.png',
            'is_territorial_limitation' => 0,
            'is_full_reduce' => 1
        ];
    }

    public function getSetting()
    {
        $default = $this->getDefault();
        $option = CommonOption::get('composition_setting', $this->mall->id, 'plugin', $default);
        $option = $this->checkDefault($option);
        $option['defaultImg'] = $default['activityBg'];
        return $option;
    }

    public function checkDefault($option)
    {
        $default = $this->getDefault();
        foreach ($default as $item => $value) {
            if (!isset($option[$item])) {
                $option[$item] = $value;
            }
        }
        return $option;
    }
}
