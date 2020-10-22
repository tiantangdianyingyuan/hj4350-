<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Model;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\Plugin;

class DiyPintuanForm extends Model
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
        if (version_compare(\Yii::$app->getAppVersion(), (new Plugin())->version) == 1) {
            return $this->getNewGoodsById($goodsIds);
        } else {
            return $this->getOldGoodsById($goodsIds);
        }
    }

    public function getOldGoodsById($goodsIds)
    {
        $goodsIds = PintuanGoods::find()->where([
            'goods_id' => $goodsIds,
            'start_time' =>  '0000-00-00 00:00:00'
        ])->andWhere(['>', 'end_time', date('Y-m-d H:i:s')])
            ->select('goods_id');

        $list = Goods::find()->where([
            'id' => $goodsIds,
            'status' => 1,
            'is_delete' => 0
        ])
            ->with('goodsWarehouse', 'groups.attr')->all();
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            if (count($item->groups) > 0) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $arr = $apiGoods->getDetail();
                $arr['people_num'] = 0;
                $arr['pintuan_price'] = 0;
                if ($item->groups) {
                    $arr['people_num'] = $item->groups[0]['people_num'];
                    $arr['pintuan_price'] = $item->groups[0]['attr'][0]['pintuan_price'];
                    $arr['price_content'] = '￥' . $item->groups[0]['attr'][0]['pintuan_price'];
                }
                $newList[] = $arr;
            }
        }

        return $newList;
    }

    public function getNewGoodsById($goodsIds)
    {
        // 有阶梯团的商品 才在前端展示
        $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])
            ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->groupBy('pintuan_goods_id')
            ->select('pintuan_goods_id');

        $goodsIds = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $pintuanGoodsIds,
            'goods_id' => $goodsIds
        ])
            ->andWhere([
                'or',
                ['end_time' => '0000-00-00 00:00:00'],
                ['>', 'end_time', mysql_timestamp()]
            ])->select('goods_id');

        $list = Goods::find()->where([
            'id' => $goodsIds,
            'status' => 1,
            'is_delete' => 0,
        ])->with('goodsWarehouse')->all();

        return $this->getGoodsList($list);
    }

    // 插件优化后废弃
    public function getNewGoods($data, $goods)
    {
        if (version_compare(\Yii::$app->getAppVersion(), (new Plugin())->version) == 1) {
            $newArr = [];
            foreach ($data['list'] as $item) {
                foreach ($goods as $gItem) {
                    try {
                        if ($item['id'] == $gItem['id']) {
                            $newArr[] = $gItem;
                            break;
                        }
                    } catch (\Exception $exception) {

                    }
                }
            }

            $data['list'] = $newArr;
        } else {
            $data = $this->getOldNewGoods($data, $goods);
        }

        return $data;
    }

    // 插件优化后废弃
    public function getOldNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as &$item) {
            foreach ($goods as $gItem) {
                try {
                    if ($item['id'] == $gItem['id']) {
                        /** @var Goods $ptGoods */
                        $ptGoods = Goods::find()->where([
                            'id' => $gItem['id']
                        ])->with('groups.attr', 'attr')->one();
                        $goodsStock = 0;
                        foreach ($ptGoods->attr as $aItem) {
                            $goodsStock += $aItem->stock;
                        }
                        foreach ($ptGoods->groups as $groupItem) {
                            foreach ($groupItem->attr as $aItem) {
                                $goodsStock += $aItem->pintuan_stock;
                            }
                        }
                        $dItem['goods_stock'] = $goodsStock;
                        $newArr[] = $gItem;
                        break;
                    }
                }catch (\Exception $exception) {

                }
            }
        }
        unset($item);

        $data['list'] = $newArr;

        return $data;
    }

    /**
     * @param $arr
     * @param Goods $item
     * @return array
     */
    public function extraGoods($arr, $item)
    {
        $goodsList = $item->getGoodsGroups($item);
        $arr['people_num'] = 0;
        $arr['pintuan_price'] = 0;
        /*** @var Goods $goods */
        foreach ($goodsList as $key => $goods) {
            if (!$key) {
                $arr['people_num'] = $goods->oneGroups->people_num;
                $arr['pintuan_price'] = $goods->attr[0]->price;
                $arr['price_content'] = '￥' . $goods->attr[0]->price;
            }
        }
        return $arr;
    }
}
