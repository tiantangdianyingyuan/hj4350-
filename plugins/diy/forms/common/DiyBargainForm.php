<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\models\Model;
use app\plugins\bargain\models\Goods;

class DiyBargainForm extends Model
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
            'is_delete' => 0
        ])->with(['goodsWarehouse', 'bargainGoods'])->all();

        return $this->getGoodsList($list);
    }

    /**
     * @param Goods $goods
     * @return bool
     */
    public function goodsValidate($goods)
    {
        if (!$goods->bargainGoods) {
            return false;
        }
        return true;
    }

    /**
     * @param $arr
     * @param Goods $goods
     * @return array
     * @throws \Exception
     */
    public function extraGoods($arr, $goods)
    {
        $bargainGoods = $goods->bargainGoods;
        $arr['price'] = $bargainGoods->min_price;
        $arr['price_content'] = $bargainGoods->min_price ? '￥' . $bargainGoods->min_price : '免费';
        $arr['start_time'] = $bargainGoods->begin_time;
        $arr['end_time'] = $bargainGoods->end_time;
        return $arr;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['id'] == $gItem['id']) {
                    $newArr[]  = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $newArr;

        return $data;
    }
}
