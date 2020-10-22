<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\common;

use app\forms\common\grafika\ApiGrafika;
use Grafika\Color;
use Grafika\Grafika;

class StyleGrafika extends ApiGrafika implements BaseConst
{
    use CommonFunc;

    public function setFile(array $option)
    {
        $arr = array_merge($option, [
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
        ]);

        $this->poster_file_name = sha1(serialize($arr)) . '.png';
        $file_url = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $this->poster_file_name);
        if (file_exists($this->temp_path . $this->poster_file_name)) {
            return $file_url;
        }
        return false;
    }

    public function getPoster(array $option)
    {
        /** var $goods_qrcode */
        foreach ($option as $item) {
            if (!isset($item['file_type'])) {
                throw new \Exception('格式错误');
            }

            if ($item['file_type'] === self::TYPE_BG) {
                if ($this->isUrl($item['image_url'])) {
                    $item['image_url'] = self::saveTempImage($this->destroyList($item['image_url']));
                }
                $this->model->open($goods_qrcode, $item['image_url']);
                $this->model->resizeExact($goods_qrcode, 750, 1334);
            }

            if ($item['file_type'] === self::TYPE_IMAGE) {
                if (array_key_exists('size', $item)) {
                }
                if ($this->isUrl($item['image_url'])) {
                    $item['image_url'] = self::saveTempImage($this->destroyList($item['image_url']));
                }
                $this->apiBlend($goods_qrcode
                    , $xx
                    , $item['image_url']
                    , $item['width']
                    , $item['height']
                    , 'normal'
                    , 1
                    , 'top-left'
                    , $item['left']
                    , $item['top']
                    , $item['mode']
                );
            }
            if ($item['file_type'] === self::TYPE_TEXT) {
                $this->apiText($goods_qrcode
                    , $item['text']
                    , $item['font']
                    , $item['left']
                    , $item['top']
                    , $item['color']
                    , $item['font_path'] ?? ''
                );
            };
            if ($item['file_type'] === self::TYPE_LINE) {
                $this->model->draw($goods_qrcode, Grafika::createDrawingObject('Line'
                    , array($item['start_x'], $item['start_y'])
                    , array($item['end_x'], $item['start_y'])
                    , array($item['height'])
                    , new Color($item['color'])
                ));
            };
            if ($item['file_type'] === self::TYPE_ELLIPSE) {
                $this->model->draw($goods_qrcode, Grafika::createDrawingObject('Ellipse'
                    , $item['width']
                    , $item['height']
                    , array($item['left']
                    , $item['top'])
                    , 0
                    , null
                    , new Color($item['color'])
                ));
            };
            if ($item['file_type'] === self::TYPE_RECTANGLE) {
                //radius
                $this->model->draw($goods_qrcode, Grafika::createDrawingObject('Rectangle'
                    , $item['width']
                    , $item['height']
                    , array($item['left']
                    , $item['top'])
                    , 0
                    , null
                    , new Color($item['color'])
                ));
            }
        }
        $this->apiSave($goods_qrcode);
        return $this;
    }
}



