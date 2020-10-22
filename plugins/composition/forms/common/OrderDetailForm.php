<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/2/28
 * Time: 15:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common;


use app\models\Model;

class OrderDetailForm extends Model
{
    public function changeOrderInfo($order)
    {
        $compositionList = [];
        foreach ($order['detail'] as $detail) {
            if (!isset($detail['goods_info']['goods_attr']['composition_data'])) {
                continue;
            }
            $composition = $detail['goods_info']['goods_attr']['composition_data'];
            if (!isset($compositionList[$composition['id']])) {
                $compositionList[$composition['id']] = [
                    'id' => $composition['id'],
                    'name' => $composition['name'],
                    'type' => $composition['type'],
                    'goods_list' => [],
                    'price' => 0,
                    'total_price' => 0,
                ];
            }
            $compositionList[$composition['id']]['goods_list'][] = [
                'name' => $detail['goods_info']['goods_attr']['name'],
                'attr_list' => $detail['goods_info']['attr_list'],
                'pic_url' => $detail['goods_info']['goods_attr']['cover_pic'],
                'num' => $detail['num'],
                'total_original_price' => $detail['total_original_price'],
                'page_url' => '',
                'composition_price' => $detail['goods_info']['goods_attr']['composition_price'], // 优惠金额
                'price' => price_format($detail['total_price'] - $detail['goods_info']['goods_attr']['composition_price']), // 套餐价格
                'total_price' => price_format($detail['total_price']), // 支付价格
                'unit_price' => price_format($detail['unit_price']), // 原价
            ];
            $compositionList[$composition['id']]['price'] += $detail['total_original_price']; // 套餐总价（没算优惠券）
            $compositionList[$composition['id']]['total_price'] += $detail['total_price']; // 套餐支付价格（计算优惠券）
        }
        $compositionList = array_values($compositionList);
        $compositionList = array_map(function ($v) {
            $v['price'] = price_format($v['price']);
            $v['total_price'] = price_format($v['total_price']);
            return $v;
        }, $compositionList);
        $order['composition_list'] = $compositionList;
        return $order;
    }
}
