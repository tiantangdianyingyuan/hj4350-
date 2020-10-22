<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;

use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\models\Model;
use app\plugins\advance\models\AdvanceGoods;
use yii\helpers\ArrayHelper;

class DiyAdvanceForm extends Model
{
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
        $list = AdvanceGoods::find()->where([
            'goods_id' => $goodsIds, 'is_delete' => 0
        ])->with(['attr.attr' ,'goods' => function ($query) {
            $query->where(['is_delete' => 0])->with(['goodsWarehouse.cats']);
        }])->all();
        try {
            $advance = \Yii::$app->plugin->getPlugin('advance');
            $config = $advance->getOrderConfig();
            if ($config['is_member_price'] == 0) {
                $advanceConfig['is_level'] = 0;
            } else {
                $advanceConfig['is_level'] = 1;
            }
        } catch (\Exception $e) {
            $advanceConfig['is_level'] = 0;
        }
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr'] = ArrayHelper::toArray($item['attr']);
            $newItem['goods'] = ArrayHelper::toArray($item['goods']);
            $newItem['goods']['goodsWarehouse'] = ArrayHelper::toArray($item['goods']['goodsWarehouse']);
            if ($item['goods']['use_attr'] == 1) {
                $minDeposit = $item['attr'][0]['attr']['deposit'];
                $minSwellDeposit = $item['attr'][0]['attr']['swell_deposit'];
                foreach ($item['attr'] as $k => $v) {
                    if ($minDeposit < $v['attr']['deposit']) {
                        $minDeposit = $v['attr']['deposit'];
                        $minSwellDeposit = $v['attr']['swell_deposit'];
                    }
                }
                $newItem['deposit'] = round($minDeposit, 2);
                $newItem['swell_deposit'] = round($minSwellDeposit, 2);
            }
            $newItem['page_url'] = '/plugins/advance/detail/detail?id=' . $item['goods_id'];
            $newItem['mch'] = $item['goods']['mch_id'];
            $newItem['is_negotiable'] = "0";
            $newItem['is_level'] = $advanceConfig['is_level'] ? $item['goods']['is_level'] : 0;
            $newItem['sign'] = $item['goods']['sign'];
            $newItem['video_url'] = $item['goods']['goodsWarehouse']['video_url'];
            $newItem['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($item['goods'])->getAppoint();
            if (empty($item['goods']) || empty($advanceConfig['is_level'])) {
                $newItem['level_price'] = -1;
            } else {
                $newItem['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice($item['goods']);
            }
            unset($newItem['attr']);
            $newList[] = $newItem;
        }
        unset($item);

        return $newList;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as &$item) {
            foreach ($goods as $gItem) {
                //商品下架
                if ($gItem['goods']['status'] == 0) {
                    continue;
                }
                //已过定金时间
                if (strtotime($gItem['end_prepayment_at']) < time()) {
                    continue;
                }
                if ($item['id'] == $gItem['goods_id']) {
                    $newArr[] = $gItem;
                    break;
                }
            }
        }

        $data['list'] = $newArr;

        return $data;
    }
}
