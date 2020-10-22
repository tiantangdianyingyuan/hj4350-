<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pintuan\forms\api\poster;


use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrders;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['goods_id' => $class->goods->id, 'user_id' => \Yii::$app->user->id, 'id' => $class->other[0]],
            240,
            current($class->other) ? 'plugins/pt/detail/detail' : 'plugins/pt/goods/goods',
        ];
    }

    public function traitMultiMapContent()
    {
        $image = [
            'file_type' => self::TYPE_IMAGE,
            'width' => 120,
            'height' => 110,
            'left' => 0,
            'top' => 0,
            'image_url' => PluginHelper::getPluginBaseAssetsUrl('pintuan') . '/img/pt-qrcode.png',
        ];
        return [$image];
    }

    public function traitHash($model)
    {
        return array_merge(['id' => $model->goods->id, $model->poster_arr], $model->other);
    }

    public function traitPrice($model, $left, $top, $has_center, $color)
    {
        if ($group_id = $model->other[0]) {

            $ptGoods = PintuanOrders::findOne([
                'id' => $group_id
            ]);

            $groups = PintuanGoodsGroups::findOne([
                'id' => $ptGoods->pintuan_goods_groups_id
            ]);
            $people_num = $ptGoods->people_num;
            $prices = array_column($groups->attr, 'pintuan_price');
        } else {
            $ptGoods = PintuanGoods::find()->where([
                'goods_id' => $model->other[1],
            ])->with(['goods.goodsWarehouse', 'goods.attr', 'ptGoodsAttr'])->one();
            $people_num = 0;
            foreach ($ptGoods->groups as $i) {
                if (!$people_num || $i->people_num < $people_num) {
                    $people_num = $i->people_num;
                    $prices = array_column($i->attr, 'pintuan_price');
                }
            }
        }

        $team = $this->setText($people_num . '人团', $left, $top + 10, 30, $color);
        $font_path = \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';
        $t = imagettfbbox($team['font'], 0, $font_path, $team['text']);
        $t_width = $t[2] - $t[0];


        $minPrice = min($prices);
        $maxPrice = max($prices);
        if ($maxPrice > $minPrice && $minPrice >= 0) {
            $mark_width = 28;
            $mark = $this->setText('￥', $left + $t_width + 3, $top + 10, 32, $color);
            $price = $this->setText($minPrice . '-' . $maxPrice, $left + $t_width + $mark_width, $top, 52, $color);

            $g = imagettfbbox($price['font'], 0, $font_path, $price['text']);
            $g_width = $g[2] - $g[0];

            $has_center && $left = (750 - $g_width - $t_width - $mark_width) / 2;


            $team['left'] = $left;
            $mark['left'] = $left + $t_width + 3;
            $price['left'] = $mark['left'] + $mark_width;
            return [
                $team,
                $mark,
                $price,
            ];
        }
        if ($maxPrice == $minPrice && $minPrice > 0) {
            $mark_width = 28;
            $mark = $this->setText('￥', $left + $t_width + 3, $top + 10, 32, $color);
            $price = $this->setText($minPrice, $left + $t_width + $mark_width, $top, 52, $color);

            $g = imagettfbbox($price['font'], 0, $font_path, $price['text']);
            $g_width = $g[2] - $g[0];

            $has_center && $left = (750 - $g_width - $t_width - $mark_width) / 2;


            $team['left'] = $left;
            $mark['left'] = $left + $t_width + 3;
            $price['left'] = $mark['left'] + $mark_width;

            return [
                $team,
                $mark,
                $price,
            ];
        }
        if ($minPrice == 0) {
            $mark = $this->setText('免费', $left + $t_width + 3, $top, 48, $color);

            $m = imagettfbbox($mark['font'], 0, $font_path, $mark['text']);
            $m_width = $m[2] - $m[0];

            $has_center && $left = (750 - $m_width - $t_width) / 2;
            $team['left'] = $left;
            $mark['left'] = $left + $t_width + 3;

            return [
                $team,
                $mark
            ];
        }
        return [];
    }
}