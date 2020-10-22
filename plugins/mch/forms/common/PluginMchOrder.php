<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\common;


use app\models\Model;
use app\models\RefundAddress;
use yii\helpers\ArrayHelper;

class PluginMchOrder extends Model
{
    public static function getRefundAddress($mchId)
    {
        $addressList = RefundAddress::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchId,
            'is_delete' => 0
        ])->all();

        $newAddressList = [];
        /** @var RefundAddress $item */
        foreach ($addressList as $item) {
            $newItem = ArrayHelper::toArray($item);
            try {
                $address = \Yii::$app->serializer->decode($item->address);
                $newAddress = '';
                foreach ($address as $aItem) {
                    $newAddress .= $aItem;
                }
                $newItem['new_address'] = $newAddress . $item->address_detail;
            } catch (\Exception $exception) {
                $newItem['new_address'] = '';
            }
            $newAddressList[] = $newItem;
        }

        return $newAddressList;
    }
}