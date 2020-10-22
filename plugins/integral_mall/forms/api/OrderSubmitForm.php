<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api;

use app\forms\api\order\OrderException;
use app\forms\common\CommonUser;
use app\models\Goods;
use app\models\User;
use app\models\UserInfo;
use app\plugins\integral_mall\forms\common\SettingForm;
use app\plugins\integral_mall\models\IntegralMallGoodsAttr;
use app\plugins\integral_mall\models\IntegralMallOrders;
use app\plugins\integral_mall\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName())->setEnablePriceEnable(false)
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false)
            ->setEnableCoupon($setting['is_coupon'] ? true : false)
            ->setEnableIntegral(false);
        return $this;
    }

    protected function getCustomCurrency($goods, $goodsAttr)
    {
        $iAttr = IntegralMallGoodsAttr::findOne(['goods_attr_id' => $goodsAttr->id, 'is_delete' => 0]);
        return [
            $iAttr->integral_num * $goodsAttr->number . '积分',
        ];
    }

    protected function getCustomCurrencyAll($listData)
    {
        $num = $listData[0]['goods_list'][0]['num'];
        $id = $listData[0]['goods_list'][0]['goods_attr']['id'];
        $iAttr = IntegralMallGoodsAttr::findOne(['goods_attr_id' => $id, 'is_delete' => 0]);

        return [$iAttr->integral_num * $num . '积分'];
    }

    public function extraOrder($order, $mchItem)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $integralCount = 0;
            foreach ($mchItem['goods_list'] as $item) {
                // 创建积分商城订单
                $integralOrder = new IntegralMallOrders();
                $integralOrder->token = \Yii::$app->security->generateRandomString();
                $integralOrder->order_id = $order->id;
                $integralOrder->mall_id = $order->mall_id;
                $integralOrder->integral_num = $item['goods_attr']['extra']['integral_num'] * $item['num'];
                $res = $integralOrder->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($integralOrder));
                }
                $integralCount += $item['goods_attr']['extra']['integral_num'] * $item['num'];
            }

            /** @var UserInfo $userInfo */
            $userInfo = CommonUser::getUserInfo();
            if ($userInfo->integral < $integralCount) {
                throw new \Exception('积分余额不足');
            }
            // 积分扣除并记录日志
            $user = User::findOne(\Yii::$app->user->id);
            $customDesc = \Yii::$app->serializer->encode($order->attributes);
            \Yii::$app->currency->setUser($user)->integral->sub(
                (int)$integralCount,
                "积分商城:兑换商品",
                $customDesc
            );

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            throw $e;
        }
    }

    // 商品规格类
    public function getGoodsAttrClass()
    {
        return new IntegralMallOrderGoodsAttr();
    }

    public function getSendType($mchItem)
    {
        $setting = (new SettingForm())->search();
        return $setting['send_type'];
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
