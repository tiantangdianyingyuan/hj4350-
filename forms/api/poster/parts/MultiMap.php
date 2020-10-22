<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\parts;


use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;

class MultiMap implements BaseConst
{
    use CommonFunc;

    private $mode = 'fill';

    public $height = 1334;
    public $width = 750;
    public $top = 0;
    public $left = 0;
    public $imageList = [];
    /** @var Arc */
    private $arc;
    private $radius = 0;
    private $angle = [];

    private $has_plugin_icon;
    private $plugin_icon = [];

    public function __construct($imageList, $height = 1334, $width = 750, $left = 0, $top = 0)
    {
        $this->imageList = $imageList;
        $this->height = $height;
        $this->width = $width;
        $this->top = $top;
        $this->left = $left;
    }

    public function setExtraMultiMap(array $data, $model)
    {
        foreach ($data as $item) {
            if ($item['file_type'] && $item['file_type'] == self::TYPE_TEXT) {
                $font_path = \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';
                $t = imagettfbbox($item['font'] / self::FONT_FORMAT, 0, $font_path, $item['text']);
                $t_height = abs($t[7] - $t[1]);
                $t_width = abs($t[2] - $t[0]);

                if (isset($item['left'])) {
                    $left = $item['left'] + $this->left;
                }
                if (isset($item['right'])) {
                    if (preg_match('/(\d+)%/', $item['right'], $matches)) {
                        $right = $this->width * $matches[1] / 100;
                    } else {
                        $right = $item['right'];
                    }
                    $left = $this->left + $this->width - $t_width - $right;
                }
                if (isset($item['top'])) {
                    /** 待加 */
                }
                if (isset($item['bottom'])) {
                    $top = $this->height + $this->top - $t_height - $item['bottom'];
                    $top = $model === 'app\\forms\\api\\poster\\style\\StyleTwo' ? $top : $top + 43;
                    array_push($this->plugin_icon, $this->setText($item['text'], $left, $top, $item['font'], $item['color']));
                }
            }

            if ($item['file_type'] && $item['file_type'] == self::TYPE_IMAGE) {
                if (preg_match('/(\d+)%/', $item['width'], $matches)) {
                    $width = $this->width * $matches[1] / 100;
                } else {
                    $width = $item['width'];
                }

                if (isset($item['left'])) {
                    $left = $item['left'] + $this->left;
                }

                if (isset($item['right'])) {
                    $left = $this->left + $this->width - $item['width'] - $item['right'];
                }

                if (isset($item['top'])) {
                    array_push($this->plugin_icon, $this->setImage($item['image_url'], $width, $item['height'], $left, $this->top + $item['top']));
                    continue;
                }
                if (isset($item['bottom'])) {
                    $selectImage = $model === 'app\\forms\\api\\poster\\style\\StyleTwo' ? $item['two_image_url'] : $item['image_url'];
                    $selectHeight = $model === 'app\\forms\\api\\poster\\style\\StyleTwo' ? $item['two_image_height'] : $item['height'];
                    array_push($this->plugin_icon, $this->setImage($selectImage, $width, $selectHeight, $left, $this->top + $this->height - $selectHeight - $item['bottom']));
                    continue;
                }
            }
        }
    }


    public function setRadius($radius, $angle)
    {
        /** TODO radius 与fill冲突 解决麻烦一点 **/
        return;
        $this->radius = $radius;
        $this->angle = $angle;
        $this->arc = new Arc();
    }

    public function create($type = 0): array
    {
        $list = $this->imageList;
        if (empty($list)) {
            throw new \Exception('图片不能为空');
        }

        while (count($list) < 5) {
            $list = array_merge($list, $list);
        }

        switch ((int)$type) {
            case 1:
                return array_merge($this->imageOne($list), $this->plugin_icon);
                break;
            case 2:
                return array_merge($this->imageTwo($list), $this->plugin_icon);
                break;
            case 3:
                return array_merge($this->imageThree($list), $this->plugin_icon);
                break;
            case 4:
                return array_merge($this->imageFour($list), $this->plugin_icon);
                break;
            case 5:
                return array_merge($this->imageFive($list), $this->plugin_icon);
                break;
            default:
                return [];
                break;
        }
    }

    private function radius($image_url, $has_left_top = false, $has_right_top = false, $has_left_bottom = false, $has_right_bottom = false)
    {
        if (!$this->radius) {
            return $image_url;
        }
        $angle = [];
        $has_left_top && array_push($angle, 'left-top');
        $has_right_top && array_push($angle, 'right-top');
        $has_left_bottom && array_push($angle, 'left-bottom');
        $has_right_bottom && array_push($angle, 'right-bottom');
        $this->arc->angle = $angle;
        return $this->arc->drawImage($image_url, $this->radius);
    }

    private function hasExists($key)
    {
        return in_array($key, $this->angle);
    }

    private function imageOne(array $list): array
    {
        return [
            $this->setImage($this->radius(current($list), $this->hasExists('left-top'), $this->hasExists('right-top'), $this->hasExists('left-bottom'), $this->hasExists('right-bottom'))
                , $this->width
                , $this->height
                , $this->left
                , $this->top
                , $this->mode
            )
        ];
    }

    private function imageTwo(array $list): array
    {
        return [
            $this->setImage($this->radius(current($list), $this->hasExists('left-top'), $this->hasExists('right-top'), false, false)
                , $this->width
                , $this->height / 2
                , $this->left
                , $this->top
                , $this->mode
            ),

            $this->setImage($this->radius(next($list), false, false, $this->hasExists('left-bottom'), $this->hasExists('right-bottom'))
                , $this->width
                , $this->height / 2
                , $this->left
                , $this->top + $this->height / 2
                , $this->mode
            ),
        ];
    }

    private function imageThree(array $list): array
    {
        return [
            $this->setImage($this->radius(current($list), $this->hasExists('left-top'), $this->hasExists('right-top'), false, false)
                , $this->width
                , $this->height / 2
                , $this->left
                , $this->top
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, $this->hasExists('left-bottom'), false)
                , $this->width / 2
                , $this->height / 2
                , $this->left
                , $this->top + $this->height / 2
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, false, $this->hasExists('left-bottom'))
                , $this->width / 2
                , $this->height / 2
                , $this->left + $this->width / 2
                , $this->top + $this->height / 2
                , $this->mode
            ),
        ];
    }

    private function imageFour(array $list): array
    {
        return [
            $this->setImage($this->radius(current($list), $this->hasExists('left-top'), false, false, false)
                , $this->width / 2
                , $this->height / 2
                , $this->left
                , $this->top
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, $this->hasExists('right-top'), false, false)
                , $this->width / 2
                , $this->height / 2
                , $this->left + $this->width / 2
                , $this->top
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, $this->hasExists('left-bottom'), false)
                , $this->width / 2
                , $this->height / 2
                , $this->left
                , $this->top + $this->height / 2
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, false, $this->hasExists('right-bottom'))
                , $this->width / 2
                , $this->height / 2
                , $this->left + $this->height / 2
                , $this->top + $this->width / 2
                , $this->mode
            ),
        ];
    }

    private function imageFive(array $list): array
    {
        return [
            $this->setImage($this->radius(current($list), $this->hasExists('left-top'), false, false, false)
                , $this->width / 2
                , $this->height / 2
                , $this->left
                , $this->top
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, $this->hasExists('left-bottom'), false)
                , $this->width / 2
                , $this->height / 2
                , $this->left
                , $this->top + $this->height / 2
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, $this->hasExists('right-top'), false, false)
                , $this->width / 2
                , $this->height / 3
                , $this->left + $this->width / 2
                , $this->top
                , $this->mode
            ),
            $this->setImage(next($list)
                , $this->width / 2
                , $this->height / 3
                , $this->left + $this->width / 2
                , $this->top + $this->height / 3
                , $this->mode
            ),
            $this->setImage($this->radius(next($list), false, false, false, $this->hasExists('right-bottom'))
                , $this->width / 2
                , $this->height / 3
                , $this->left + $this->width / 2
                , $this->top + $this->height / 3 * 2
                , $this->mode
            ),
        ];
    }
}