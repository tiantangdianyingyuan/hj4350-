<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\common;

use app\models\Goods;
use app\models\Model;
use app\plugins\quick_share\models\QuickShareSetting;

class CommonQuickShare extends Model
{
    /**
     * @param null $mall_id
     * @return QuickShareSetting|array|null
     */
    public static function getSetting($mall_id = null)
    {
        if (!isset($mall_id)) {
            $mall_id = \Yii::$app->mall->id;
        }

        $setting = QuickShareSetting::findOne(['mall_id' => $mall_id]);
        $default = [
            'title' => '素材中心',
            'type' => 0,
            'goods_poster' => CommonPoster::getPosterDefault()
        ];

        if ($setting) {
            $setting['goods_poster'] = $setting['goods_poster'] ? \yii\helpers\Json::decode($setting['goods_poster']) : $default['goods_poster'];
        } else {
            $setting = $default;
        }
        return $setting;
    }

    /**
     * @param Goods $goods
     * @return array|null
     */
    public static function getExtraInfo(Goods $goods, $sales)
    {
        $data = null;
        $id = null;
        try {
            $quickShareGoods = CommonGoods::getGoods($id, $goods->id, 1);
            $data = [
                'share_text' => $quickShareGoods['share_text'],
                'share_pic' => \yii\helpers\BaseJson::decode($quickShareGoods->share_pic),
                'mall_name' => \Yii::$app->mall->name,
                'format_time' => date('Y-m-d', strtotime($quickShareGoods->created_at))
            ];
        } catch (\Exception $e) {
            $setting = self::getSetting();
            if ($setting['type'] == 1) {
                $data = [
                    'share_pic' => \yii\helpers\BaseJson::decode($goods->goodsWarehouse->pic_url),
                    'share_text' => $goods->goodsWarehouse->name,
                    'mall_name' => \Yii::$app->mall->name,
                    'format_time' => date('Y-m-d', strtotime($goods->created_at))
                ];
            }
        }
        return $data;
    }
}
