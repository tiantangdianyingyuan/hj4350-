<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\parts;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;

class Arc implements BaseConst
{
    use Convert, CommonFunc;

    private const ANGLE_LIST = ['left-top', 'left-bottom', 'right-bottom', 'right-top'];

    public $angle = [];
    public $radius = 10;


    private function condition($point, $height, $width, $radius)
    {
        $angle = array_unique($this->angle);

        if (in_array(self::ANGLE_LIST[0], $angle)) {
            $circle = [
                'center' => ['x' => $radius, 'y' => $radius],
                'radius' => $radius
            ];
            if ($point['x'] < $radius && $point['y'] < $radius && !$this->is_point_in_circle($point, $circle)) {
                return true;
            }
        }
        if (in_array(self::ANGLE_LIST[1], $angle)) {
            $circle = [
                'center' => ['x' => $radius, 'y' => $height - $radius],
                'radius' => $radius
            ];
            if ($point['x'] < $radius && $point['y'] > $height - $radius && !$this->is_point_in_circle($point, $circle)) {
                return true;
            }
        }

        if (in_array(self::ANGLE_LIST[2], $angle)) {
            $circle = [
                'center' => ['x' => $width - $radius, 'y' => $height - $radius],
                'radius' => $radius
            ];
            if ($point['x'] > $width - $radius && $point['y'] > $height - $radius && !$this->is_point_in_circle($point, $circle)) {
                return true;
            }
        }

        if (in_array(self::ANGLE_LIST[3], $angle)) {
            $circle = [
                'center' => ['x' => $width - $radius, 'y' => $radius],
                'radius' => $radius
            ];
            if ($point['x'] > $width - $radius && $point['y'] < $radius && !$this->is_point_in_circle($point, $circle)) {
                return true;
            }
        }
        return false;
    }

    public function createRectangle($width, $height, $left, $top, $color)
    {
        $params = [
            'width' => $width,
            'height' => $height,
            'left' => $left,
            'top' => $top,
            'color' => $color,
        ];

        if (!is_dir(\Yii::$app->runtimePath . '/image')) {
            mkdir(\Yii::$app->runtimePath . '/image');
        }
        $dest_path = \Yii::$app->runtimePath . '/image/' . sha1(serialize($params)) . '.png';
        if (file_exists($dest_path)) {
            return [$this->setImage($dest_path, $width, $height, $left, $top)];
        }

        $radius = $this->radius;
        $newImage = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        $rgb = hex2rgb($color);
        $fillingColor = imagecolorallocate($newImage, $rgb['r'], $rgb['g'], $rgb['b']);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                if ($this->condition(['x' => $x, 'y' => $y], $height, $width, $radius)) {
                    imagesetpixel($newImage, $x, $y, $transparent);
                } else {
                    imagesetpixel($newImage, $x, $y, $fillingColor);
                }
            }
        }

        imagesavealpha($newImage, true);
        imagepng($newImage, $dest_path);
        imagedestroy($newImage);

        return [$this->setImage($dest_path, $width, $height, $left, $top)];
    }

    public function drawImage($image_url, $radius)
    {
        $params = [
            'image_url' => $image_url,
            'radius' => $radius,
        ];

        $dest_path = \Yii::$app->basePath . '/runtime/image/' . sha1(serialize($params)) . '.png';
        if (file_exists($dest_path)) {
            return $dest_path;
        }

        list($width, $height) = getimagesize($image_url);
        $src = imagecreatefromstring(file_get_contents($image_url));

        $newImage = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                if ($this->condition(['x' => $x, 'y' => $y], $height, $width, $radius)) {
                    imagesetpixel($newImage, $x, $y, $transparent);
                } else {
                    $color = imagecolorat($src, $x, $y);
                    imagesetpixel($newImage, $x, $y, $color);
                }

            }
        }
        imagesavealpha($newImage, true);
        imagepng($newImage, $dest_path);
        imagedestroy($newImage);

        unset($image_url);
        return $dest_path;
    }
}