<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\common;

trait CommonFunc
{
    final public function setBg($image_url)
    {
        return [
            'file_type' => self::TYPE_BG,
            'is_show' => 1,
            'image_url' => $image_url,
        ];
    }

    /**
     * @param string $text 文字
     * @param int $left 左间距
     * @param int $top 上间距
     * @param string $font 字体大小
     * @param string $color 颜色
     * @return array
     */
    final public function setText($text, $left, $top, $font, $color): array
    {
        return [
            'file_type' => self::TYPE_TEXT,
            'is_show' => 1,
            'text' => $text,
            'left' => $left,
            'top' => $top,
            'color' => $color,
            'font' => round($font / self::FONT_FORMAT, 3),
        ];
    }

    /**
     * @param string $image_url
     * @param int $width
     * @param int $height
     * @param int $left
     * @param int $top
     * @param string $mode
     * @return array
     */
    final public function setImage($image_url, $width, $height, $left, $top, $mode = 'exact'): array
    {
        return [
            'file_type' => self::TYPE_IMAGE,
            'height' => $height,
            'width' => $width,
            'left' => $left,
            'top' => $top,
            'is_show' => 1,
            'image_url' => $image_url,
            'mode' => $mode,
        ];
    }

    /**
     * @param array $start
     * @param array $end
     * @param string $color
     * @param int $height
     * @return array
     */
    final public function setLine($start, $end, $color = '#353535', $height = 1)
    {
        return [
            'file_type' => self::TYPE_LINE,
            'start_x' => current($start),
            'start_y' => next($start),
            'end_x' => current($end),
            'end_y' => next($end),
            'height' => $height,
            'color' => $color,
            'is_show' => 1,
        ];
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $left
     * @param int $top
     * @param string $color
     * @return array
     */
    final public function setEllipse($width, $height, $left, $top, $color = '#353535')
    {
        return [
            'file_type' => self::TYPE_ELLIPSE,
            'is_show' => 1,
            'width' => $width,
            'height' => $height,
            'left' => $left,
            'top' => $top,
            'color' => $color
        ];
    }
}