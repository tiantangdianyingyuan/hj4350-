<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderSubmitForm;
use app\models\Coupon;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use app\plugins\exchange\models\ExchangeGoods;

class EOrderSubmitForm extends OrderSubmitForm
{
    public $form_data;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    public function getConfig()
    {
        $commonSetting = new CommonSetting();
        $setting = $commonSetting->get();
        $this->setEnableMemberPrice(intval($setting['is_member_price']) === 1)
            ->setEnableCoupon(intval($setting['is_coupon']) === 1)
            ->setEnableIntegral(intval($setting['is_integral']) === 1)
            ->setEnableOrderForm(false)
            ->setEnableAddressEnable(false)
            ->setEnablePriceEnable(false)
            ->setEnableVipPrice(intval($setting['svip_status']) === 1)
            ->setSupportPayTypes($setting['payment_type'])
            ->setSign('exchange')
            ->setEnableFullReduce($setting['is_full_reduce'] ? true : false)
            ->preview();
        return $this;
    }

    public function hasRecipient()
    {
        return false;
    }

    public function getIsECardGoods($mchItem)
    {
        return true;
    }

    public function setGoodsForm($mchItem)
    {
        $defaultForm = null;
        $existsFormIds = [];
        $hasGoodsForm = false;
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            $goodsItem['form'] = null;
        }
        $mchItem['diff_goods_form_count'] = intval(count($existsFormIds));
        $mchItem['has_goods_form'] = $hasGoodsForm;
        return $mchItem;
    }

    protected function isPluginCouponUsable($mchItem, $userCoupon)
    {
        $coupon = Coupon::findOne($userCoupon->coupon_id);
        return $coupon->appoint_type == 5;
    }

    protected function checkGoods($goods, $item)
    {
        $exchangeGoods = ExchangeGoods::findOne([
            'goods_id' => $goods->id,
        ]);
        if (!$exchangeGoods) {
            throw new \Exception('商品不存在');
        }
        $f = new FacadeAdmin();
        $f->validate->libraryModel = $exchangeGoods->library;
        $f->validate->hasLibrary();
        $f->validate->hasDisableLibrary();
        //兑换码有效期判断
        if (
            $f->validate->libraryModel->expire_type === 'fixed'
            && $f->validate->libraryModel->expire_end_time < date('Y-m-d H:i:s')
        ) {
            throw new OrderException('无法生成有效兑换码');
        }
        return true;
    }

    protected function getGoodsItemData($item)
    {
        $itemData = parent::getGoodsItemData($item);
        $itemData['pieces'] = 0;
        $itemData['forehead'] = 0;
        $itemData['freight_id'] = 0;
        return $itemData;
    }

    public function getSendType($mchItem)
    {
        return ['none'];
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
