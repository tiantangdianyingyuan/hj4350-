<?php

namespace app\plugins\region\handlers;


use app\forms\mall\delivery\DeliveryForm;
use app\handlers\HandlerBase;
use app\models\DistrictArr;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\models\Store;
use app\plugins\region\events\OrderEvent;
use app\plugins\region\forms\common\CommonForm;
use app\plugins\region\models\RegionOrder;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionSetting;


class OrderSalesHandler extends HandlerBase
{

    public function register()
    {
        \Yii::$app->on(
            Order::EVENT_SALES,
            function ($event) {
                /* @var OrderEvent $event */
                //多商户订单过滤
                if ($event->order->mch_id) {
                    return;
                }
                \Yii::error('-------------区域代理开始执行--------------');

                $setting = RegionSetting::getList($event->order->mall_id);
                if ($setting['is_region'] != 1) {
                    \Yii::error('区域代理未开启');
                    return;
                }

                if ((empty($event->order->address) || $event->order->address === '') && $event->order->send_type != 1) {
                    \Yii::error('无地址，不分红');
                    return;
                }

                if ($event->order->send_type == 1) {
                    \Yii::error('自提订单分红，门店ID' . $event->order->store_id);

                    $store_info = Store::findOne($event->order->store_id);
                    if (empty($store_info)) {
                        \Yii::error('自提订单门店失踪，不分红');
                        return;
                    }
                    try {
                        if ($store_info->province_id > 0 && $store_info->city_id > 0 && $store_info->district_id > 0) {
                            $event->order->address = DistrictArr::getDistrict($store_info->province_id)['name'];
                            $event->order->address .= ' ' . DistrictArr::getDistrict($store_info->city_id)['name'];
                            $event->order->address .= ' ' . DistrictArr::getDistrict($store_info->district_id)['name'];
                        } else {
                            $event->order->address = address_handle($store_info->address);
                        }
                    } catch (\Exception $exception) {
                        \Yii::error('门店地址不规范，无法识别');
                        return;
                    }
                }
                if ($event->order->send_type == 2) {
                    $city_setting = (new DeliveryForm())->getData();
                    $address = $city_setting['data']['list']['address']['address'];
                    if (empty($address)) {
                        \Yii::error('同城派送地址未设置');
                        return;
                    }
                    $event->order->address = address_handle($address);
                }
                $arr_address = explode(' ', $event->order->address);
                $data = [
                    'province' => $arr_address[0],
                    'city' => $arr_address[1],
                    'district' => $arr_address[2],
                ];
                CommonForm::getAddressId($data);
                if (RegionRelation::find()->andWhere(
                        [
                            'in',
                            'district_id',
                            [$data['province_id'] ?? 0, $data['city_id'] ?? 0, $data['district_id'] ?? 0]
                        ]
                    )
                        ->andWhere(['mall_id' => $event->order->mall_id, 'is_delete' => 0, 'is_update' => 0])->count() <= 0) {
                    \Yii::error('该订单区域没有代理，不记录分红订单池');
                    return;
                }

                $t = \Yii::$app->db->beginTransaction();
                try {
                    \Yii::error('区域代理订单记录事件开始：');
                    //查询售后退款
                    $refund_price = OrderRefund::find()->where(
                            [
                                'order_id' => $event->order->id,
                                'status' => 2,
                                'is_confirm' => 1,
                                'is_delete' => 0,
                                'is_refund' => 1
                            ]
                        )
                            ->andWhere(['!=', 'type', 2])->sum('reality_refund_price') ?? 0;
                    //记录订单分红金额
                    $model = new RegionOrder();
                    $model->mall_id = $event->order->mall_id;
                    $model->order_id = $event->order->id;
                    $model->total_pay_price = bcsub($event->order->total_pay_price, $refund_price);

                    $model->province = $arr_address[0];
                    $model->city = $arr_address[1];
                    $model->district = $arr_address[2];

                    $model->province_id = $data['province_id'] ?? 0;
                    $model->city_id = $data['city_id'] ?? 0;
                    $model->district_id = $data['district_id'] ?? 0;
                    if (!$model->save()) {
                        throw new \Exception((new Model())->getErrorMsg($model));
                    }
                    \Yii::error('区域代理订单记录事件结束：ID-' . $model->id);

                    $t->commit();
                } catch (\Exception $exception) {
                    $t->rollBack();
                    \Yii::error('订单过售后区域代理事件：');
                    \Yii::error($exception);
                    throw $exception;
                }
            }
        );
    }
}
