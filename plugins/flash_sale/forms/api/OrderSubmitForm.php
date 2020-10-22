<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/15
 * Time: 11:03
 */

namespace app\plugins\flash_sale\forms\api;

use app\plugins\flash_sale\forms\common\CommonSetting;
use app\plugins\flash_sale\Plugin;
use Yii;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    private $pluginSetting;

    public function setPluginData()
    {
        $setting = (new CommonSetting())->search();
        $this->pluginSetting = $setting;
        $this->setSign((new Plugin())->getName());
        $mallPaymentTypes = Yii::$app->mall->getMallSettingOne('payment_type');
        $this->setSupportPayTypes($mallPaymentTypes);
        $this->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false);

        return $this;
    }

    public function checkGoods($goods, $item)
    {
        if ($goods->sign != (new Plugin())->getName()) {
            return parent::checkGoods($goods, $item);
        }

        return Yii::$app->plugin->getPlugin($goods->sign)->checkGoods($goods, $item);
    }

    public function isGoodsEnableMemberPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableMemberPrice($goodsItem);
        } else {
            return parent::isGoodsEnableMemberPrice($goodsItem);
        }
    }

    public function isGoodsEnableVipPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableVipPrice($goodsItem);
        } else {
            return parent::isGoodsEnableVipPrice($goodsItem);
        }
    }

    public function isGoodsEnableIntegral($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableIntegral($goodsItem);
        } else {
            return parent::isGoodsEnableIntegral($goodsItem);
        }
    }

    public function isGoodsEnableCoupon($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableCoupon($goodsItem);
        } else {
            return parent::isGoodsEnableCoupon($goodsItem);
        }
    }

    public function isGoodsEnableFullReduce($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableFullReduce($goodsItem);
        } else {
            return parent::isGoodsEnableCoupon($goodsItem);
        }
    }
}
