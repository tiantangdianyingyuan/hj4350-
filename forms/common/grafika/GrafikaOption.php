<?php


namespace app\forms\common\grafika;

use Grafika\Color;
use Grafika\Grafika;

class GrafikaOption extends ApiGrafika
{
    protected function optionDiff($option, $default): array
    {
        foreach ($default as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if ($option && array_key_exists($k, $option) && array_key_exists($k1, $option[$k])) {
                    if($k == 'bg_pic' && $k1 == 'url' && !$option[$k][$k1])
                        continue;
                    $default[$k][$k1] = $option[$k][$k1];
                }
                if ($k1 == 'width' || $k1 == 'height' || $k1 == 'size' || $k1 == 'top' || $k1 == 'left') {
                    $default[$k][$k1] = (float)$default[$k][$k1] * 2;
                } else if ($k1 == 'font') {
                    $default[$k][$k1] = (float)$default[$k][$k1] * 1.48;
                } else if ($k1 == 'color' && !$this->isHex2($default[$k][$k1])) {
                    $default[$k][$k1] = RGBToHex($default[$k][$k1]);
                } else if ($k == 'bg_pic' && $k1 == 'url' && $this->isUrl($default[$k][$k1])) {
                    $default[$k][$k1] = self::saveTempImage($this->destroyList($default[$k][$k1]));
                };
            }

            //destroy
            if (array_key_exists('is_show', $default[$k]) && $default[$k]['is_show'] == '0' && $k !== 'bg_pic') {
                unset($default[$k]);
            }
        }
        return $default;
    }

    public function setFile(array $option) {
        $key = $option;
        if(array_key_exists('qr_code', $option)) {
            unset($key['qr_code']['file_path']);
        }
        if(array_key_exists('head', $option)) {
            unset($key['head']['file_path']);
        }
        $keys = array_merge(
            $key,
            [
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
            ]
        );
        $this->poster_file_name = sha1(serialize($keys)) . '.jpg';
        $file_url = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $this->poster_file_name);
        if (file_exists($this->temp_path . $this->poster_file_name)) {
            return $file_url;
        }
        return false;
    }

    public function getCache(array $option)
    {
        if ($file_url = $this->setFile($option)) {
            return $file_url;
        }
        return false;
    }

    public function getPoster(array $option)
    {
        foreach ($option as $k => $v) {
            if ($k == 'bg_pic') {
                if($this->isUrl($option[$k]['url'])) {
                    $option[$k]['url'] = self::saveTempImage($this->destroyList($option[$k]['url']));
                }
                $this->model->open($goods_qrcode, $option[$k]['url']);
                $this->model->resizeExact($goods_qrcode, 750, 1334);
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'image') {
                if(array_key_exists('size', $option[$k])){
                    $option[$k]['width'] = $option[$k]['size'];
                    $option[$k]['height'] = $option[$k]['size'];
                }
                if($this->isUrl($option[$k]['file_path'])) {
                    $option[$k]['file_path'] = self::saveTempImage($this->destroyList($option[$k]['file_path']));
                }
                $this->apiBlend($goods_qrcode, $xx, $option[$k]['file_path'], $option[$k]['width'], $option[$k]['height'], 'normal', 1, 'top-left', $option[$k]['left'], $option[$k]['top']);
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'text') {
                $this->apiText($goods_qrcode, $option[$k]['text'], $option[$k]['font'], $option[$k]['left'], $option[$k]['top'], $option[$k]['color'], $option[$k]['font_path'] ?? '');
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'line') {
                $this->model->draw($goods_qrcode, Grafika::createDrawingObject('Rectangle', $option[$k]['width'], $option[$k]['height'], array($option[$k]['left'], $option[$k]['top']), 0, null, new Color($option[$k]['color'])));
            }
        }
        $this->apiSave($goods_qrcode);
        return $this;
    }
}