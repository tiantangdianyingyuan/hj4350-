<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;

use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\models\ExchangeLibrary;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCardDetail;

class CommonModel
{
    public static function getCardGoods(int $goods_id): ?ExchangeGoods
    {
        return ExchangeGoods::findOne([
            'goods_id' => $goods_id,
            'mall_id' => \Yii::$app->mall->id,
        ]);
    }

    public static function getLibrary($id, $mall_id = '', $where = []): ?ExchangeLibrary
    {
        $mall_id || $mall_id = \Yii::$app->mall->id;
        return ExchangeLibrary::find()->where([
            'id' => $id,
            'mall_id' => $mall_id,
            'is_delete' => 0
        ])->andWhere($where)->one();
    }

    public static function getFormatRewards($rewards): array
    {
        if (is_string($rewards)) {
            $rewards = \yii\helpers\BaseJson::decode($rewards);
        }

        $create = [];
        $types = ExchangeLibrary::defaultType();
        for (
            $i = 0; $i < count($rewards);
            $i++
        ) {
            if (isset($types[$rewards[$i]['type']])) {
                $item = array_merge($types[$rewards[$i]['type']], $rewards[$i]);
                if ($item['type'] === 'goods') {
                    $attr_id = $item['attr_id'];
                    $attr = GoodsAttr::findOne($attr_id);
                    if ($attr && $goods = $attr->goods) {
                        $attr_list = (new Goods())->signToAttr($attr['sign_id'], $goods['attr_groups']);
                        $attr_str = '';
                        foreach ($attr_list as $item1) {
                            $attr_str .= $item1['attr_group_name'] . ':' . $item1['attr_name'] . ';';
                        }
                        $item['goods_info'] = [
                            'name' => $goods['name'],
                            'attr_str' => trim($attr_str, ';'),
                            'cover_pic' => $goods->coverPic,
                        ];
                    }
                }
                if ($item['type'] === 'coupon') {
                    $coupon = Coupon::findOne($item['coupon_id']);
                    if ($coupon) {
                        $item['coupon_info'] = [
                            'name' => $coupon->name,
                            'discount' => $coupon->discount,
                            'type' => $coupon->type,
                            'min_price' => $coupon->min_price,
                            'sub_price' => $coupon->sub_price,
                            'discount_limit' => $coupon->discount_limit,
                        ];
                    }
                }
                if ($item['type'] === 'integral') {
                    //$item['integral_info'] = [];
                }
                if ($item['type'] === 'balance') {
                    //$item['balance_info'] = [];
                }
                if ($item['type'] === 'svip') {
                    $vip = VipCardDetail::findOne($item['child_id']);
                    if ($vip) {
                        $vipCard = CommonVip::getCommon()->getMainCard($vip->vip_id);
                        $item['svip_info'] = [
                            'name' => $vip->name,
                            'discount' => $vipCard->discount,
                        ];
                    }
                }
                if ($item['type'] === 'card') {
                    $card = GoodsCards::findOne($item['card_id']);
                    if ($card) {
                        $item['card_info'] = [
                            'name' => $card->name,
                            'pic_url' => $card->pic_url,
                            'number' => $card->number,
                        ];
                    }
                }
                array_push($create, $item);
            }
        }
        return $create;
    }

    public static function setFormatRewards($rewards): array
    {
        if (is_string($rewards)) {
            $rewards = \yii\helpers\BaseJson::decode($rewards);
        }
        $default = ExchangeLibrary::defaultType();
        $newData = [];
        foreach ($rewards as $reward) {
            if (!is_array($reward)) {
                throw new \Exception('格式错误');
            }
            if (isset($default[$reward['type']])) {
                $arr = $default[$reward['type']];

                $reward = array_filter($reward, function ($item, $name) use ($arr) {
                    return in_array($name, array_keys($arr));
                }, ARRAY_FILTER_USE_BOTH);
                $newItem = array_merge($arr, $reward, ['token' => md5(uniqid(md5(microtime(true)), true))]);

                if (
                    ($newItem['type'] === 'integral' && $newItem['integral_num'] == 0)
                    ||
                    ($newItem['type'] === 'balance' && $newItem['balance'] == 0)
                    ||
                    ($newItem['type'] === 'svip' && $newItem['child_id'] == '')
                    ||
                    ($newItem['type'] === 'card' && ($newItem['card_id'] == '' || $newItem['card_num'] == ''))
                    ||
                    ($newItem['type'] === 'coupon' && ($newItem['coupon_id'] == '' || $newItem['coupon_num'] == ''))
                    ||
                    ($newItem['type'] === 'goods' && ($newItem['attr_id'] == '' || $newItem['goods_num'] == ''))
                ) {
                    throw new \Exception(sprintf('%s不能为空', $arr['name']));
                }
                array_push($newData, $newItem);
            }
        }
        return $newData;
    }

    public static function getStatus($libraryModel, $codeModel, &$msg = '')
    {
        if (in_array($codeModel['status'], [2, 3])) {
            $msg = '已兑换';
            return 2;
        }
        if ($codeModel['status'] == 0) {
            $msg = '已禁用';
            return 0;
        }

        $date = date('Y-m-d H:i:s');
        if (
            $libraryModel['expire_type'] !== 'all' && $codeModel['valid_end_time'] < $date
        ) {
            // || $codeModel['valid_start_time'] > $date
            $msg = '过期';
            return -1;
        }

        $msg = '可用';
        return 1;
    }

    public static function getPlatform($platform)
    {
        switch ($platform) {
            case APP_PLATFORM_WXAPP:
                return '微信';
            case APP_PLATFORM_ALIAPP:
                return '支付宝';
            case APP_PLATFORM_BDAPP:
                return '百度';
            case APP_PLATFORM_TTAPP:
                return '抖音/头条';
            default:
                return '后台';
        }
    }
}
