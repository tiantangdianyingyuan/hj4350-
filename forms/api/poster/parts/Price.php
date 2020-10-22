<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\parts;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\models\Goods;

class Price implements BaseConst
{
    use CommonFunc;

    public $font_path;
    private $top;
    private $left;
    private $has_center;
    private $color;
    public function __construct($left = 28 + 24, $top = 702 + 225 + 50, $has_center = false, $color = '#ff4544')
    {
        $this->left = $left;
        $this->top = $top;
        $this->has_center = $has_center;
        $this->color = $color;
        $this->font_path = \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';
    }

    private function handleCenter($text, $font, $other_width = 0)
    {
        if ($this->has_center) {
            $this->left = 0;
            $t = imagettfbbox($font / self::FONT_FORMAT, 0, $this->font_path, $text);
            $t_width = $t[2] - $t[0];
            $this->left = (750 - $t_width - $other_width) / 2;
        }
    }

    private function drawMark(&$mark_width)
    {
        $mark_width = 28;
        return $this->setText('￥', $this->left, $this->top + 10, 32, $this->color);
    }

    public function create(Goods $goods): array
    {
        $is_negotiable = $goods->mallGoods->is_negotiable ?? 0;
        if ($is_negotiable) {
            $this->handleCenter('价格面议', 48);
            return [
                $this->setText('价格面议', $this->left, $this->top, 48, $this->color)
            ];
        }

        $prices = array_column($goods->attr, 'price');
        if (empty($prices)) {
            throw new \Exception('海报-商品规格异常');
        }

        $minPrice = min($prices);
        $maxPrice = max($prices);
        if ($maxPrice > $minPrice && $minPrice >= 0) {
            $this->handleCenter($minPrice . '-' . $maxPrice, 52, 28);
            return [
                $this->drawMark($mark_width),
                $this->setText($minPrice . '-' . $maxPrice, $this->left + $mark_width, $this->top, 52, $this->color)
            ];
        }
        if ($maxPrice == $minPrice && $minPrice > 0) {
            $this->handleCenter($minPrice, 52, 28);
            return [
                $this->drawMark($mark_width),
                $this->setText($minPrice, $this->left + $mark_width, $this->top, 52, $this->color)
            ];
        }
        if ($minPrice == 0) {
            $this->handleCenter('免费', 48);
            return [
                $this->setText('免费', $this->left, $this->top, 48, $this->color)
            ];
        }
        return [];
    }
}
