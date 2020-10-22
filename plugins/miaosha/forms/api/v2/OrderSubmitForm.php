<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api\v2;

use app\forms\api\order\OrderException;
use app\models\OrderDetail;
use app\plugins\miaosha\forms\common\v2\SettingForm;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    private $pluginSetting;

    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->pluginSetting = $setting;
        $this->setSign((new Plugin())->getName());
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        $this->setSupportPayTypes($mallPaymentTypes);
        $this->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false);

        return $this;
    }

    public function checkGoods($goods, $item)
    {
        if ($goods->sign != (new Plugin())->getName()) {
            return parent::checkGoods($goods, $item);
        }
        return \Yii::$app->plugin->getPlugin($goods->sign)->checkGoods($goods, $item);
    }

    public function isGoodsEnableMemberPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'miaosha') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableMemberPrice($goodsItem);
        } else {
            return parent::isGoodsEnableMemberPrice($goodsItem);
        }
    }

    public function isGoodsEnableVipPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'miaosha') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableVipPrice($goodsItem);
        } else {
            return parent::isGoodsEnableVipPrice($goodsItem);
        }
    }

    public function isGoodsEnableIntegral($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'miaosha') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableIntegral($goodsItem);
        } else {
            return parent::isGoodsEnableIntegral($goodsItem);
        }
    }

    public function isGoodsEnableCoupon($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'miaosha') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableCoupon($goodsItem);
        } else {
            return parent::isGoodsEnableCoupon($goodsItem);
        }
    }

    public function isGoodsEnableFullReduce($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'miaosha') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableFullReduce($goodsItem);
        } else {
            return parent::isGoodsEnableCoupon($goodsItem);
        }
    }
}
