<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\forms\api\poster\common\BaseConst;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\helpers\PluginHelper;
use app\models\Mall;

class PosterForm extends GrafikaOption implements BaseConst
{
    public $page_id;

    public function rules()
    {
        return [
            [['page_id'], 'required'],
            [['page_id'], 'integer'],
        ];
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    private function get()
    {
        $option = $this->getOption();

        $mall = (new Mall())->getMallSetting(['mall_logo_pic']);
        if ($picUrl = current($mall)) {
            $option['mallBigPic']['file_path'] = $picUrl;
            $option['mallPic']['file_path'] = $picUrl;
        }

        if ($option['mallName']['text']) {
            $text = imagettfbbox($option['mallName']['font'], 0, $this->font_path, $option['mallName']['text']);
            $option['mallName']['left'] = (750 - $text[2] + $text[0]) / 2;
        }

        $cache = $this->getCache(array_merge($option, [
            'page_id' => $this->page_id,
        ]));

        if ($cache) {
            return ['pic_url' => parse_url($cache)['path'] . '?v=' . time()];
        }

        //画圆
        if ($picUrl) {
            $circleUrl = self::avatar(self::saveTempImage($picUrl), $this->temp_path);
            $option['mallBigPic']['file_path'] = $circleUrl;
            $option['mallPic']['file_path'] = $circleUrl;
        }

        $option['qr_code']['file_path'] = self::qrcode($option, [
            ['page_id' => $this->page_id],
            240,
            'pages/index/index',
        ], $this);
        $editor = $this->getPoster($option);
        return ['pic_url' => parse_url($editor->qrcode_url)['path'] . '?v=' . time()];
    }

    protected function getOption()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('diy') . '/images/';

        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 618,
                'height' => 1179,
                'top' => (1334 - 1179) / 2,
                'left' => (750 - 618) / 2,
                'file_path' => $iconBaseUrl . 'poster-bg.png',
                'file_type' => 'image',
            ],
            'headTitle' => [
                'is_show' => '1',
                'font' => 28 / self::FONT_FORMAT,
                'top' => 142,
                'left' => 140,
                'text' => '发现一家好店',
                'color' => '#666666',
                'file_type' => 'text',
            ],
            'mallBg' => [
                'is_show' => '1',
                'width' => 652,
                'height' => 496,
                'top' => 190,
                'left' => 49,
                'file_path' => $iconBaseUrl . 'poster-shop-bg.png',
                'file_type' => 'image',
            ],
            'mallBigPic' => [
                'is_show' => '1',
                'width' => 327,
                'height' => 329,
                'top' => 273,
                'left' => 212,
                'file_path' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster-big-shop.png',
                'file_type' => 'image',
            ],
            'mallPic' => [
                'is_show' => '1',
                'width' => 80,
                'height' => 80,
                'top' => 789,
                'left' => 340,
                'file_path' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster-big-shop.png',
                'file_type' => 'image',
            ],
            'mallName' => [
                'is_show' => '1',
                'font' => 36 / self::FONT_FORMAT,
                'top' => 887,
                'left' => 0,
                'color' => '#333333',
                'text' => \Yii::$app->mall->name,
                'file_type' => 'text',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 160,
                'top' => 995,
                'left' => 295,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 24 / self::FONT_FORMAT,
                'top' => 1167,
                'left' => 276,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],
        ];
    }

    private function qrcode(array $option, array $params, $model)
    {
        $qrocde = new CommonQrCode();
        $qrocde->appPlatform = APP_PLATFORM_WXAPP;
        $code = $qrocde->getQrCode($params[0], $params[1], $params[2]);
        $code_path = self::saveTempImage($code['file_path']);
        if ($option['qr_code']['type'] == 1) {
            $code_path = self::wechatCode($code_path, $model->temp_path, $option['qr_code']['size'], $option['qr_code']['size']);
        }
        return $model->destroyList($code_path);
    }
}
