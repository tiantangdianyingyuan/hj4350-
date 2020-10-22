<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\parts;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;

class PosterBg implements BaseConst
{
    use CommonFunc;
    const COLOR_LIST = ['#fc4a3b', '#ff7b22', '#ffc73f', '#3e9b6a', '#4bb1ff', '#8548ef', '#000000', '#a0a0a0', '#FFFFFF'];
    const TYPE_LIST = [1, 2]; //1纯色 2渐变

    private $default_file_path;
    private $file_path;

    public $color;
    public $type;

    public function __construct($color = '', $type = '')
    {
        $this->default_file_path = \Yii::$app->basePath . '/web/statics/img/mall/poster_bg.png';
        $this->file_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/tpl-bg/';

        $this->color = $color;
        $this->type = (int)$type;
    }

    public function create()
    {
        return [$this->setBg($this->getUrl())];
    }

    private function getUrl()
    {
        if (in_array($this->color, self::COLOR_LIST)) {
            if (self::TYPE_LIST[0] === $this->type) {
                switch ($this->color) {
                    case self::COLOR_LIST[0]:
                        return $this->file_path . 'red.png';
                        break;
                    case self::COLOR_LIST[1]:
                        return $this->file_path . 'orange.png';
                        break;
                    case self::COLOR_LIST[2]:
                        return $this->file_path . 'yellow.png';
                        break;
                    case self::COLOR_LIST[3]:
                        return $this->file_path . 'green.png';
                        break;
                    case self::COLOR_LIST[4]:
                        return $this->file_path . 'blue.png';
                        break;
                    case self::COLOR_LIST[5]:
                        return $this->file_path . 'violet.png';
                        break;
                    case self::COLOR_LIST[6]:
                        return $this->file_path . 'black.png';
                        break;
                    case self::COLOR_LIST[7]:
                        return $this->file_path . 'gray.png';
                        break;
                    case self::COLOR_LIST[8]:
                        return $this->file_path . 'white.png';
                        break;
                }
            }
            if (self::TYPE_LIST[1] === $this->type) {
                switch ($this->color) {
                    case self::COLOR_LIST[0]:
                        return $this->file_path . 'red_white.png';
                        break;
                    case self::COLOR_LIST[1]:
                        return $this->file_path . 'orange_white.png';
                        break;
                    case self::COLOR_LIST[2]:
                        return $this->file_path . 'yellow_white.png';
                        break;
                    case self::COLOR_LIST[3]:
                        return $this->file_path . 'green_white.png';
                        break;
                    case self::COLOR_LIST[4]:
                        return $this->file_path . 'blue_white.png';
                        break;
                    case self::COLOR_LIST[5]:
                        return $this->file_path . 'violet_white.png';
                        break;
                    case self::COLOR_LIST[6]:
                        return $this->file_path . 'black_white.png';
                        break;
                    case self::COLOR_LIST[7]:
                        return $this->file_path . 'gray_white.png';
                        break;
                    case self::COLOR_LIST[8]:
                        return $this->file_path . 'white.png';
                        break;
                }
            }
        }
        return $this->default_file_path;
    }
}