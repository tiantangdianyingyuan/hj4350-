<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\common;


use app\models\Model;
use app\models\OrderDetail;

class PluginMchGoods extends Model
{
    /**
     * 处理订单展示的商品数据
     * @param OrderDetail $orderDetail
     * @return array
     */
    public static function getGoodsData($orderDetail)
    {
        $goodsInfo = [];
        try {
            $goodsAttrInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
            $goodsInfo['name'] = isset($goodsAttrInfo['goods_attr']['name']) ? $goodsAttrInfo['goods_attr']['name'] : '';
            $goodsInfo['attr_list'] = isset($goodsAttrInfo['attr_list']) ? $goodsAttrInfo['attr_list'] : [];
            $goodsInfo['pic_url'] = isset($goodsAttrInfo['goods_attr']['pic_url']) && $goodsAttrInfo['goods_attr']['pic_url'] ? $goodsAttrInfo['goods_attr']['pic_url'] : $goodsAttrInfo['goods_attr']['cover_pic'];

            $goodsInfo['num'] = isset($orderDetail->num) ? $orderDetail->num : 0;
            $goodsInfo['total_original_price'] = isset($orderDetail->total_original_price) ? $orderDetail->total_original_price : 0;
            $goodsInfo['member_discount_price'] = isset($orderDetail->member_discount_price) ? $orderDetail->member_discount_price : 0;

            try {
                $sign = $orderDetail->order && $orderDetail->order->sign ? $orderDetail->order->sign  : 'wxapp';
                $plugins = \Yii::$app->plugin->getPlugin($sign);
                if (is_callable(array($plugins, 'getGoodsUrl'))) {
                    $goodsInfo['page_url'] = $plugins->getGoodsUrl($orderDetail->goods);
                } else {
                    $goodsInfo['page_url'] = '';
                }
            }catch (\Exception $exception) {
                $goodsInfo['page_url'] = '';
            }

        } catch (\Exception $exception) {
            // dd($exception);
        }
        return $goodsInfo;
    }
}