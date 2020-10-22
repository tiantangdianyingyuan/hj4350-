<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\api;

use app\forms\api\order\OrderSubmitForm;
use app\models\Form;
use app\models\Store;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonBookingGoods;
use app\plugins\booking\models\BookingGoods;
use app\plugins\booking\models\BookingStore;

class BookingOrderSubmitForm extends OrderSubmitForm
{
    public $form_data;
    public function afterGetMchItem(&$mchItem)
    {
        $mchItem['show_delivery'] = false;
        $mchItem['show_express_price'] = false;
        $mchItem = $this->setOrderForm($mchItem);
        return parent::afterGetMchItem($mchItem);
    }

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    protected function setDeliveryData($mchItem, $formMchItem)
    {
        $mchItem['delivery'] = [
            'disabled' => true,
            'send_type' => 'offline',
            'send_type_list' => [
                [
                    'name' => '仅自提',
                    'value' => 'offline',
                ],
            ],
        ];
        return $mchItem;
    }


    public function whiteList()
    {
        return [$this->sign];
    }

    protected function setGoodsForm($mchItem)
    {
        return $mchItem;
    }

    protected function setOrderForm($mchItem)
    {
        $mchItem['order_form'] = null;
        if ($mchItem['mch']['id'] != 0) {
            return $mchItem;
        }
        $goods_id = $mchItem['goods_list'][0]['id'];
        /** @var BookingGoods $goods */
        $goods = CommonBookingGoods::getGoods($goods_id);
        if (!$goods) {
            return $mchItem;
        }

        $newOrderForm = [];
        if ($goods->is_order_form) {
        // 开启下单表单 且 用商城的表单表单
            if ($goods->order_form_type == 1) {
                // 商城默认表单
                /** @var Form $form */
                if ($goods->goods->form_id == 0) {
                    $form = Form::find()->where([
                        'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->user->identity->mch_id,
                        'status' => 1,
                        'is_default' => 1
                    ])->one();
                } else {
                    $form = Form::find()->where([
                        'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->user->identity->mch_id,
                        'status' => 1,
                        'id' => $goods->goods->form_id
                    ])->one();
                }
                if ($form) {
                    // 商品自定义表单
                    $newOrderForm = \yii\helpers\BaseJson::decode($form->value) ?: [];
                }
            } else {
                // 商品自定义表单
                $newOrderForm = \yii\helpers\BaseJson::decode($goods->form_data) ?: [];
            }
        } else {
            // 商品未开启下单表单 则用预约全局
            $setting = CommonBooking::getSetting();
            if ($setting['is_form']) {
                $newOrderForm = $setting['form_data'];
            }
        }

        foreach ($newOrderForm as $k => $item) {
            $newOrderForm[$k]['is_required'] = $item['is_required'] == 1 ? 1 : 0;
        }
        $mchItem['order_form'] = [
            'name' => '',
            'status' => 1,
            'value' => $newOrderForm,
        ];

        return $mchItem;
    }

    protected function setStoreData($mchItem, $formMchItem, $formData)
    {
        $mchItem['store'] = null;
        $mchItem['store_select_enable'] = true;

        if (
            !empty($formData['longitude'])
            && !empty($formData['latitude'])
            && is_numeric($formData['longitude'])
            && is_numeric($formData['latitude'])
        ) {
            $select = [
                '*',
                '(st_distance(point(longitude, latitude), point('
                . $formData['longitude']
                . ','
                . $formData['latitude']
                . ')) * 111195) as distance'
            ];
        } else {
            $select = ['*'];
        }
        ////
        $store = BookingStore::find()->select('store_id')
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $mchItem['goods_list'][0]['id'],
                'is_delete' => 0,
            ])->asArray()->all();
        if (!$store) {
            return $mchItem;
        }
        $ids = array_column($store, 'store_id');

        if (!empty($formMchItem['store_id'])) {
            $store = Store::find()
                ->select($select)
                ->where([
                    'id' => $formMchItem['store_id'],
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ])->andWhere(['in', 'id', $ids])->asArray()->one();
        } else {
            $store = Store::find()
                ->select($select)
                ->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_default' => 1,
                    'is_delete' => 0,
                ])->andWhere(['in', 'id', $ids])->asArray()->one();
        }
        if (!$store) {
            return $mchItem;
        }
        if (!empty($store['distance']) && is_numeric($store['distance'])) {
            // $store['distance'] 单位 m
            if ($store['distance'] > 1000) {
                $store['distance'] = number_format($store['distance'] / 1000, 2) . 'km';
            } else {
                $store['distance'] = number_format($store['distance'], 0) . 'm';
            }
        } else {
            $store['distance'] = '-m';
        }
        $mchItem['store'] = $store;
        return $mchItem;
    }
}
