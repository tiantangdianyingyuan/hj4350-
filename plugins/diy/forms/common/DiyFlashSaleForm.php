<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/11
 * Time: 14:36
 */

namespace app\plugins\diy\forms\common;

use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\flash_sale\forms\common\CommonActivity;
use app\plugins\flash_sale\forms\common\CommonGoods;
use app\plugins\flash_sale\models\Goods;

class DiyFlashSaleForm extends Model
{
    use TraitGoods;

    public function getGoodsIds($data)
    {
        $goodsIds = [];
        foreach ($data['list'] as $item) {
            $goodsIds[] = $item['id'];
        }

        return $goodsIds;
    }

    public function getGoodsById($goodsIds)
    {
        if (!$goodsIds) {
            return [];
        }
        $list = Goods::find()->where([
            'id' => $goodsIds,
            'status' => 1,
            'is_delete' => 0,
        ])->with('goodsWarehouse', 'flashSaleGoods.activity', 'attr.attr')->all();

        return $this->getGoodsList($list);
    }

    /**
     * @param Goods $goods
     * @return bool
     */
    public function goodsValidate($goods)
    {
        if (
            $goods->flashSaleGoods->activity->end_at <= mysql_timestamp() ||
            $goods->flashSaleGoods->activity->status == 0
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param $arr
     * @param Goods $goods
     * @return array
     */
    public function extraGoods($arr, $goods)
    {
        $count = intval($goods['sales']) + intval($goods['virtual_sales']);
        foreach ($goods->attr as $key => $value) {
            $arr['attr'][$key]['attr'] = $value->attr;
        }
        $arr['page_url'] = '/plugins/flash_sale/goods/goods?id=' . $goods->flashSaleGoods->goods_id;
        list($discountType, $minDiscount, $minPrice) = CommonGoods::getMinDiscount($goods);
        $arr['price'] = $minPrice;
        if ($discountType == 1) {
            $discount = (1 - $minDiscount / 10) * $minPrice;
            if (isset($arr['level_price']) && $arr['level_price'] != -1) {
                $discountLevel = (1 - $minDiscount / 10) * $arr['level_price'];
                $arr['level_price'] -= min($discountLevel, $arr['level_price']);
                $arr['level_price'] = price_format($arr['level_price'], 'string', 2);
            }
        } else {
            $discount = $minDiscount;
            if (isset($arr['level_price']) && $arr['level_price'] != -1) {
                $arr['level_price'] -= min($discount, $arr['level_price']);
                $arr['level_price'] = price_format($arr['level_price'], 'string', 2);
            }
        }
        $arr['price'] -= min($discount, $arr['price']);
        $arr['price'] = price_format($arr['price'], 'string', 2);
        $arr['price_content'] = $this->getPriceContent($arr['is_negotiable'], $arr['price']);
        $arr['min_discount'] = $minDiscount;
        $arr['discount_type'] = $discountType;
        $arr['percentage'] = CommonGoods::getPercentage($count, $goods['goods_stock']);
        $arr['sales'] = '已抢购' . $count . $goods->goodsWarehouse->unit;
        $arr['time_status'] = CommonActivity::timeSlot($goods->flashSaleGoods->activity);
        $arr['flash_sale_time'] = strtotime($goods->flashSaleGoods->activity->end_at) - time();
        $arr['start_time'] = $goods->flashSaleGoods->activity->start_at;
        $arr['end_time'] = $goods->flashSaleGoods->activity->end_at;
        return $arr;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['id'] == $gItem['id']) {
                    $newArr[] = $gItem;
                    break;
                }
            }
        }

        $data['list'] = $newArr;

        return $data;
    }

    /**
     * @param int $isNegotiable
     * @param string $minPrice
     * @return string
     * 获取售价文字版
     */
    private function getPriceContent($isNegotiable, $minPrice)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($minPrice > 0) {
            $priceContent = '￥' . $minPrice;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }
}
