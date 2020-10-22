<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\integral_mall\forms\common\CouponListForm;
use app\plugins\integral_mall\models\Goods;

class DiyIntegralMallForm extends Model
{
    use TraitGoods;

    public function getGoodsIds($data)
    {
        $goodsIds = [];
        // 显示商品
        if ($data['showGoods']) {
            foreach ($data['list'] as $item) {
                $goodsIds[] = $item['id'];
            }
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
            'is_delete' => 0
        ])->with('goodsWarehouse', 'integralMallGoods')->all();

        return $this->getGoodsList($list);
    }

    public function getNewGoods($data, $goods)
    {
        $newGoodsList = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['id'] == $gItem['id']) {
                    $newGoodsList[] = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $data['showGoods'] ? $newGoodsList : [];

        if ($data['showCoupon']) {
            $common = new CouponListForm();
            $common->limit = 10;
            $res = $common->getCouponList();

            $newList = [];
            foreach ($res['list'] as $item) {
                $arr = $item['coupon'];
                $arr['page_url'] = '/plugins/integral_mall/coupon/coupon?id=' . $item['id'];
                $arr['is_receive'] = '0';
                $newList[] = $arr;
            }

            $data['coupon_list'] = $newList;
        }

        return $data;
    }
}
