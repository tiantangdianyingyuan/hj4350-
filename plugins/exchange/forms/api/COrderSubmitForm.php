<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderSubmitForm;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;

class COrderSubmitForm extends OrderSubmitForm
{
    public $form_data;
    public $lotteryLog;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    public function getConfig()
    {
        $this->setEnableMemberPrice(false)
            ->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setEnableAddressEnable(true)
            ->setEnablePriceEnable(true)
            ->setEnableVipPrice(false)
            ->setSupportPayTypes((new CommonSetting())->get()['payment_type'])
            ->setSign('exchange');
        return $this;
    }

    protected function checkGoods($goods, $item)
    {
        try {
            $info = current($this->form_data['list']);
            $code = $info['code'];
            $token = $info['token'];
            $f = new FacadeAdmin();
            $f->user(\Yii::$app->user->id);
            $f->cover(\Yii::$app->mall->id, $code);
            $rewards = \yii\helpers\BaseJson::decode($f->validate->codeModel->r_rewards);
            $f->token($rewards, $token, ['goods']);
        } catch (\Exception $e) {
            throw new OrderException($e->getMessage());
        }
        foreach ($rewards as $reward) {
            if (
                $reward['type'] === 'goods'
                && intval($reward['goods_id']) == intval($item['id'])
                && intval($reward['attr_id']) === intval($item['goods_attr_id'])
                && intval($reward['goods_num']) === intval($item['num'])
            ) {
                return $reward;
            }
        }
        throw new OrderException('非法请求');
    }
    protected function getGoodsItemData($item)
    {
        $itemData = parent::getGoodsItemData($item);
        $itemData['forehead_integral'] = 0;
        $itemData['forehead_integral_type'] = 0;
        $itemData['accumulative'] = 0;
        $itemData['pieces'] = 0;
        $itemData['forehead'] = 0;

        $itemData['total_original_price'] = 0;
        $itemData['total_price'] = 0;

        $itemData['discounts'] = [];
        $itemData['is_level_alone'] = 0;
        return $itemData;
    }
}
