<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\composition\models\Composition;

class DiyCompositionForm extends Model
{
    use TraitGoods;

    public function getGoodsIds($data)
    {
        $ids = [];
        foreach ($data['list'] as $item) {
            $ids[] = $item['composition_id'];
        }
        return $ids;
    }

    public function getGoodsById($goodsIds)
    {
        if (!$goodsIds) {
            return [];
        }
        $query = Composition::find()->where([
            'id' => $goodsIds,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods.goods.attr'])
            ->page($pagination)
            ->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /** @var Composition $composition */
        foreach ($list as $composition) {
            $coverPicList = [];
            $goodsList = [];
            $mainPrice = null;
            $countPrice = 0;
            foreach ($composition->compositionGoods as $item) {
                $arr = $this->getGoodsDetail($item->goods);
                $arr['original_price'] = '';// 不显示原价
                $arr['is_level'] = 0;
                $arr['page_url'] = '/plugins/composition/detail/detail?composition_id=' . $composition->id;

                $countPrice += $arr['price'];
                array_push($goodsList, $arr);

                if ($item['is_host']) {
                    $mainPrice = $arr['price'];
                    array_unshift($coverPicList, $arr['cover_pic']);
                } else {
                    array_push($coverPicList, $arr['cover_pic']);
                }
            }
            $newList[] = [
                'composition_id' => $composition->id,
                'page_url' => '/plugins/composition/detail/detail?composition_id=' . $composition->id,
                'name' => $composition->name,
                'price' => is_null($mainPrice) ? $countPrice : $mainPrice,
                'price_content' => $this->getPriceContent(0, is_null($mainPrice) ? $countPrice : $mainPrice),
                'cover_pic_list' => $coverPicList,
                'type' => $composition->type,
                'tag' => $composition->type != 1 ? $composition->type == 2 ? '搭配套餐' : '' : '固定套餐',
                'goods_list' => $goodsList,
            ];
        }
        return $newList;
    }

    public function getPriceContent($isNegotiable, $minPrice)
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

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['composition_id'] == $gItem['composition_id']) {
                    $newArr[] = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $newArr;
        return $data;
    }

}