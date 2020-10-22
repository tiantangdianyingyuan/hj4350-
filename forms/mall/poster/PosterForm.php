<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\poster;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOptionP;
use app\models\Model;

class PosterForm extends Model
{
    public function getDetail()
    {
        $poster = CommonAppConfig::getPosterConfig();
        $newPoster = [];
        foreach ($poster as $key => $item) {
            $newPoster[$key] = (new CommonOptionP())->poster($item, $this->getDefault()[$key]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newPoster,
            ]
        ];
    }

    public function getDefault()
    {
        $urlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/poster/';
        return [
            'share' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
                    'is_show' => '1',
                ],
                'head' => [
                    'is_show' => '1',
                    'size' => 60,
                    'top' => 10,
                    'left' => 10,
                    'file_type' => 'image',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 120,
                    'top' => 150,
                    'left' => 127,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'name' => [
                    'is_show' => '1',
                    'font' => 20,
                    'top' => 30,
                    'left' => 80,
                    'color' => '#000',
                    'file_type' => 'text',
                ],
            ],
            'goods' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
                    'is_show' => '1',
                ],
                'pic' => [
                    'url' => $urlPrefix . 'default_goods.jpg',
                    'is_show' => '1',
                    'width' => 375,
                    'height' => 375,
                    'top' => 0,
                    'left' => 0,
                    'file_type' => 'image',
                ],
                'head' => [
                    'is_show' => '1',
                    'size' => 30,
                    'top' => 550,
                    'left' => 20,
                    'file_type' => 'image',
                ],
                'nickname' => [
                    'is_show' => '1',
                    'font' => 18,
                    'top' => 557,
                    'left' => 59,
                    'color' => '#000',
                    'file_type' => 'text',
                ],
                'name' => [
                    'is_show' => '1',
                    'font' => 20,
                    'top' => 390,
                    'left' => 20,
                    'color' => '#000',
                    'file_type' => 'text',
                ],
                'price' => [
                    'is_show' => '1',
                    'font' => 20,
                    'top' => 420,
                    'left' => 20,
                    'color' => 'rgb(255, 69, 68)',
                    'file_type' => 'text',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 120,
                    'top' => 515,
                    'left' => 230,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'desc' => [
                    'is_show' => '1',
                    'width' => 200,
                    'font' => 16,
                    'top' => 595,
                    'left' => 20,
                    'color' => 'rgb(169, 169, 169)',
                    'text' => '长按识别小程序码进入',
                    'file_type' => 'text',
                ],
            ],
            'topic' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
                    'is_show' => '1',
                ],
                'pic' => [
                    'url' => $urlPrefix . 'default_goods.jpg',
                    'is_show' => '1',
                    'width' => 335,
                    'height' => 167,
                    'top' => 90,
                    'left' => 20,
                    'file_type' => 'image',
                ],
                'title' => [
                    'is_show' => '1',
                    'font' => 26,
                    'top' => 40,
                    'left' => 20,
                    'color' => '#000',
                    'file_type' => 'text',
                ],
                'look' => [
                    'is_show' => '1',
                    'font' => 16,
                    'top' => 280,
                    'left' => 20,
                    'color' => '#999',
                    'file_type' => 'text',
                ],
                'content' => [
                    'is_show' => '1',
                    'font' => 18,
                    'top' => 320,
                    'left' => 20,
                    'color' => '#666666',
                    'file_type' => 'text',
                ],
                'open_desc' => [
                    'is_show' => '1',
                    'font' => 20,
                    'top' => 410,
                    'left' => 85,
                    'color' => '#D68543',
                    'text' => '打开小程序阅读全文',
                    'file_type' => 'text',
                ],
                'desc' => [
                    'is_show' => '1',
                    'width' => 205,
                    'font' => 18,
                    'top' => 550,
                    'left' => 20,
                    'color' => '#000',
                    'text' => '长按识别小程序码进入',
                    'file_type' => 'text',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 114,
                    'top' => 510,
                    'left' => 234,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'line' => [
                    'is_show' => '1',
                    'width' => 335,
                    'height' => 1,
                    'top' => 480,
                    'left' => 20,
                    'color' => '#e2e2e2',
                    'file_type' => 'line',
                ]
            ],
            'footprint' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/footprint/footprint_poster_bg.png',
                    'is_show' => '1',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 160,
                    'top' => 1140,
                    'left' => 540,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'text_one' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 380,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '1',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_1' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 380,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店共购买',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_2' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 443,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '590',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_3' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 455,
                    'left' => 295,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '件商品',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_two' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 535,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '2',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_4' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 535,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店共消费',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_5' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 600,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '￥',
//                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_6' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 588,
                    'left' => 157,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '888888',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_three' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 677,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '3',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_7' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 677,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店最高一次消费达',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_8' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 742,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '￥',
//                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_9' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 730,
                    'left' => 157,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '168888',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
            ],
        ];
    }
}
