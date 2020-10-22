<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\goods;

use app\models\Model;
use app\models\OrderDetail;

class MallGoods extends Model
{
    /**
     * 处理订单展示的商品数据
     * @param OrderDetail $orderDetail
     * @return array
     */
    public static function getGoodsData($orderDetail)
    {
        // 暂时先处理下, TODO 应该限制orderDetail类型
        if (is_array($orderDetail)) {
            $orderDetail = (object)$orderDetail;
        }
        if ($orderDetail->order && is_array($orderDetail->order)) {
            $orderDetail->order = (object)$orderDetail->order;
        }

        $goodsInfo = [];
        try {
            $goodsAttrInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
            $goodsInfo['name'] = isset($goodsAttrInfo['goods_attr']['name']) ? $goodsAttrInfo['goods_attr']['name'] : '';
            $goodsInfo['attr_list'] = isset($goodsAttrInfo['attr_list']) ? $goodsAttrInfo['attr_list'] : [];
            $goodsInfo['pic_url'] = isset($goodsAttrInfo['goods_attr']['pic_url']) && $goodsAttrInfo['goods_attr']['pic_url'] ? $goodsAttrInfo['goods_attr']['pic_url'] : $goodsAttrInfo['goods_attr']['cover_pic'];

            $goodsInfo['num'] = isset($orderDetail->num) ? $orderDetail->num : 0;
            $goodsInfo['total_original_price'] = isset($orderDetail->total_original_price) ? $orderDetail->total_original_price : 0;
            $goodsInfo['total_price'] = isset($orderDetail->total_price) ? $orderDetail->total_price : 0;
            $goodsInfo['member_discount_price'] = isset($orderDetail->member_discount_price) ? $orderDetail->member_discount_price : 0;
            $goodsInfo['goods_attr'] = $goodsAttrInfo['goods_attr'];

            try {
                $sign = $orderDetail->order && $orderDetail->order->sign ? $orderDetail->order->sign : 'wxapp';
                if ($orderDetail->order->mch_id > 0) {
                    $sign = 'mch';
                }
                $plugins = \Yii::$app->plugin->getPlugin($sign);
                if (is_callable(array($plugins, 'getGoodsUrl'))) {
                    $goodsInfo['page_url'] = $plugins->getGoodsUrl($orderDetail->goods);
                } else {
                    $goodsInfo['page_url'] = '';
                }

            } catch (\Exception $exception) {
                $goodsInfo['page_url'] = '';
            }
            $goodsInfo['is_show_send_type'] = 1;
            $goodsInfo['is_can_apply_sales'] = 1;
            $goodsInfo['is_show_express'] = 1;
            $goodsInfo['goods_type'] = 'goods';
            if (isset($goodsAttrInfo['goods_attr']['goods_type'])) {
                $goodsInfo['is_show_send_type'] = $goodsAttrInfo['goods_attr']['goods_type'] == 'ecard' ? 0 : 1;
                $goodsInfo['is_can_apply_sales'] = $goodsAttrInfo['goods_attr']['goods_type'] == 'ecard' ? 0 : 1;
                $goodsInfo['is_show_express'] = $goodsAttrInfo['goods_attr']['goods_type'] == 'ecard' ? 0 : 1;
                $goodsInfo['goods_type'] = $goodsAttrInfo['goods_attr']['goods_type'];
            }

        } catch (\Exception $exception) {
            // dd($exception);
        }
        return $goodsInfo;
    }
}
