<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/14
 * Time: 14:36
 */

namespace app\plugins\diy\forms\common;

use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\pick\models\Goods;

class DiyPickForm extends Model
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
        ])->with('goodsWarehouse', 'pickGoods.activity')->all();

        return $this->getGoodsList($list);
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
     * @param Goods $goods
     * @return bool
     */
    public function goodsValidate($goods)
    {
        if (
            $goods->pickGoods->activity->end_at <= mysql_timestamp() ||
            $goods->pickGoods->activity->start_at > mysql_timestamp() ||
            $goods->pickGoods->activity->status == 0
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
        $arr['rule_num'] = $goods->pickGoods->activity->rule_num;
        $arr['rule_price'] = $goods->pickGoods->activity->rule_price;
        $arr['page_url'] = '/plugins/pick/detail/detail?goods_id=' . $goods->pickGoods->goods_id;
        return $arr;
    }
}
