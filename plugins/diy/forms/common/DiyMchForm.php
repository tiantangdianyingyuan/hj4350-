<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsStatistic;
use app\forms\common\order\CommonOrderStatistic;
use app\models\Goods;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGoods;
use app\plugins\mch\Plugin;

class DiyMchForm extends Model
{
    public function getMchData($data)
    {
        $mchIds = [];
        $mchGoodsIds = [];
        foreach ($data['list'] as $item) {
            $mchIds[] = $item['id'];
            // 显示商品
            if ($data['showGoods']) {
                // 自定义商品
                if ($item['staticGoods']) {
                    foreach ($item['goodsList'] as $gItem) {
                        $mchGoodsIds[] = $gItem['id'];
                    }
                }
            }
        }

        return [
            'mchIds' => $mchIds,
            'mchGoodsIds' => $mchGoodsIds
        ];
    }

    public function getMch($mchIds)
    {
        $list = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $mchIds,
            'is_delete' => 0
        ])->with('store')->all();

        $newList = [];
        /** @var Mch $item */
        foreach ($list as $item) {
            $arr['id'] = $item->id;
            $arr['name'] = $item->store->name;
            $arr['pic_url'] = $item->store->cover_url;
            $arr['longitude'] = $item->store->longitude;
            $arr['latitude'] = $item->store->latitude;

            $form = new CommonGoodsStatistic();
            $form->mch_id = $item->id;
            $form->sign = (new Plugin())->getName();
            $res = $form->getAll(['goods_count']);

            $arr['goods_num'] = $res['goods_count'];
            $newList[] = $arr;
        }

        return $newList;
    }

    public function getMchGoodsById($mchGoodsId)
    {
        $form = new CommonGoodsList();
        $form->goods_id = $mchGoodsId;
        $form->relations = ['goodsWarehouse'];
        $form->sign = (new Plugin())->getName();
        $form->limit = count($mchGoodsId);
        $form->status = 1;
        $list = $form->search();
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $arr['id'] = $item->id;
            $arr['name'] = $item->getName();
            $arr['picUrl'] = $item->getCoverPic();
            $arr['price'] = $item->getPrice();
            $arr['goodsWarehouse']['video_url'] = $item->getVideoUrl();
            $newList[] = $arr;
        }

        return $newList;
    }

    public function getNewMch($data, $diyMchGoods, $diyMch)
    {
        $newMch = [];
        foreach ($data['list'] as $key => $item) {
            $isMch = false;
            foreach ($diyMch as $mch) {
                if ($item['id'] == $mch['id']) {
                    $s = new CommonOrderStatistic();
                    $s->mch_id = $mch['id'];
                    $s->sign = 'mch';
                    $s->is_user = 1;
                    $ress = $s->getAll(['order_goods_count']);
                    $item['order_num'] = $ress['order_goods_count'];
                    $item['name'] = $mch['name'];
                    $item['pic_url'] = $mch['pic_url'];
                    $item['goods_num'] = $mch['goods_num'];
                    $item['longitude'] = $mch['longitude'];
                    $item['latitude'] = $mch['latitude'];
                    $newMch[] = $item;
                    $isMch = true;
                    break;
                }
            }
            // 显示商品
            if ($isMch && $data['showGoods']) {
                $newGoodsArr = [];
                if ($item['staticGoods']) {
                    // 自定义商品
                    foreach ($item['goodsList'] as $gItem) {
                        foreach ($diyMchGoods as $diyMchGood) {
                            if ($gItem['id'] == $diyMchGood['id']) {
                                $newGoodsArr[] = $diyMchGood;
                                break;
                            }
                        }
                    }
                } else {
                    // 默认商品
                    $goodsIds = MchGoods::find()->where([
                        'mch_id' => $item['id']
                    ])->orderBy(['sort' => SORT_ASC])->select('goods_id');

                    $list = Goods::find()->where([
                        'id' => $goodsIds,
                        'status' => 1,
                        'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id,
                    ])->with('goodsWarehouse')->page($pagination, $item['showGoodsNum'])->all();

                    $newList = [];
                    /** @var Goods $lItem */
                    foreach ($list as $lItem) {
                        $arr['id'] = $lItem->id;
                        $arr['name'] = $lItem->getName();
                        $arr['picUrl'] = $lItem->getCoverPic();
                        $arr['price'] = $lItem->getPrice();
                        $arr['goodsWarehouse']['video_url'] = $lItem->getVideoUrl();
                        $newList[] = $arr;
                    }
                    $newGoodsArr = $newList;
                }

                foreach ($newMch as $mKey => $mItem) {
                    if ($mItem['id'] == $item['id']) {
                        $newMch[$mKey]['goodsList'] = $newGoodsArr;
                    }
                }
            }
        }

        $data['list'] = $newMch;
        return $data;
    }
}
