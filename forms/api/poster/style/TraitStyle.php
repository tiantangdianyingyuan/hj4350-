<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\style;

use app\forms\api\poster\parts\Arc;
use app\forms\api\poster\parts\MultiMap;
use app\forms\api\poster\parts\PosterBg;
use app\forms\api\poster\parts\Price;
use app\forms\common\CommonQrCode;
use app\models\Goods;

trait TraitStyle
{
    /** @var Goods * */
    public $goods;
    public $typesetting;
    public $type;
    public $color;
    public $poster_arr = [];

    //pintuan
    public $defaultText = [
        'mark_one_text' => '长按识别小程序码进入',
        'mark_two_text' => '长按识别小程序码进入',
        'mark_three_text' => '长按识别小程序码 即可查看~',
        'mark_four_text' => '长按识别小程序码进入',
        'head_three_text' => '帮我看看咋样啊~',
        'end_two_remark' => '',
        'explanation_one' => '向您推荐一个好物',
        'explanation_four' => '向您推荐一个好物',
    ];

    public $other; //插件其他信息
    public $extraModel; //插件model
    private $pluginModel; //实例化后的model

    private function selectPlugin($func, ...$params)
    {
        if (!is_object($this->pluginModel)) {
            if (class_exists($this->extraModel)) {
                $this->pluginModel = new $this->extraModel;
            } else {
                return false;
            }
        }
        if (method_exists($this->pluginModel, $func)) {
            return $this->pluginModel->$func($this, ...$params);
        } else {
            return false;
        }
    }

    protected function getBg()
    {
        $model = new PosterBg($this->color, $this->type);
        $this->poster_arr = $model->create();
        return $this;
    }

    protected function getMultiMap($image_width, $image_height, $image_left, $image_top, $radian = [0, []])
    {
        if (($pic_list = $this->selectPlugin('traitMultiMap')) === false) {
            $pic_list = $this->goods->goodsWarehouse->pic_url;
            $pic_list = \yii\helpers\BaseJson::decode($pic_list);
            $pic_list = array_column($pic_list, 'pic_url');
        }

        $multiMap = new MultiMap($pic_list, $image_width, $image_height, $image_left, $image_top);
        current($radian) === 0 || $multiMap->setRadius($radian[0], $radian[1]);

        if (($extraMultimap = $this->selectPlugin('traitMultiMapContent')) !== false) {
            $multiMap->setExtraMultiMap($extraMultimap, get_class());
        }

        $arr = $multiMap->create($this->typesetting);

        $this->poster_arr = array_merge($this->poster_arr, $arr);
        return $this;
    }

    protected function getPrice($left, $top, $has_center = false,$color = '#FF4544')
    {
        if (($arr = $this->selectPlugin('traitPrice', $left, $top, $has_center, $color)) === false) {
            $model = new Price($left, $top, $has_center, $color);
            $arr = $model->create($this->goods);
        }

        $this->poster_arr = array_merge($this->poster_arr, $arr);
        return $this;
    }

    protected function getRectangle($width, $height, $left, $top, $color = '#FFFFFF', $radian = [0, []])
    {
        $arc = new Arc();
        $arc->radius = $radian[0];
        $arc->angle = $radian[1];
        $rectangle = $arc->createRectangle($width, $height, $left, $top, $color);
        $this->poster_arr = array_merge($this->poster_arr, $rectangle);
        return $this;
    }

    protected function taskHash()
    {
        if (($hashArr = $this->selectPlugin('traitHash')) === false) {
            $hashArr = array_merge(['id' => $this->goods->id, $this->poster_arr]);
        }
        return $hashArr;
    }

    protected function takeQrcode($class)
    {
        if (($params = $this->selectPlugin('traitQrcode')) === false) {
            $params = [
                ['id' => $this->goods->id, 'user_id' => \Yii::$app->user->id],
                240,
                'pages/goods/goods'
            ];
        }

        $code = (new CommonQrCode())->getQrCode($params[0], $params[1], $params[2]);
        //转本地读取 无后缀
        $path = parse_url($code['file_path'])['path'];
        $arr = explode('/', $path);
        $code_path = \Yii::$app->basePath . '/web/temp/' . end($arr);
        //$code_path = self::saveTempImage($code['file_path']);
        if (\Yii::$app->appPlatform === APP_PLATFORM_WXAPP) {
            $code_path = self::wechatCode($code_path, $class->temp_path, 240, 240);
        }
        return $class->destroyList($code_path);
    }
}