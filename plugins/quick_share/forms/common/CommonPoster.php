<?php

namespace app\plugins\quick_share\forms\common;

class CommonPoster
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }
        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 375,
                'height' => 375,
                'top' => 0,
                'left' => 0,
                'file_type' => 'image',
            ],
            'name' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 390,
                'left' => 20,
                'text' => '',
                'color' => '#353535',
                'file_type' => 'text',
            ],
//            'price' => [
//                'is_show' => '0',
//                'font' => 20,
//                'top' => 475,
//                'left' => 20,
//                'text' => '',
//                'color' => 'rgb(255, 69, 68)',
//                'file_type' => 'text',
//            ],
            'desc' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 580,
                'left' => 20,
                'width' => 205,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 120,
                'top' => 530,
                'left' => 230,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
        ];
    }

    public static function getGoods()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }
        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 364,
                'height' => 364,
                'top' => 155.5,
                'left' => 5.5,
                'file_type' => 'image',
            ],
            'name' => [
                'is_show' => '1',
                'font' => 19.8,
                'top' => 35,
                'left' => 24,
                'text' => '',
                'color' => '#212121',
                'file_type' => 'text',
            ],
            'name_two' => [
                'is_show' => '1',
                'font' => 19.8,
                'top' => 56.5,
                'left' => 24,
                'text' => '',
                'color' => '#212121',
                'file_type' => 'text',
            ],
            'price_desc' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 97,
                'left' => 24,
                'text' => '￥',
                'color' => '#ff4544',
                'file_type' => 'text',
            ],
            'price' => [
                'is_show' => '1',
                'font' => 33.6,
                'top' => 88.5,
                'left' => 40,
                'text' => '',
                'color' => '#ff4544',
                'file_type' => 'text',
            ],
            'original_price' => [
                'is_show' => '1',
                'font' => 14.4,
                'top' => 100,
                'left' => 0,
                'text' => '',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'sales' => [
                'is_show' => '1',
                'font' => 14.4,
                'top' => 120,
                'left' => 24,
                'text' => '',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 580,
                'left' => 40,
                'width' => 205,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 120,
                'top' => 530,
                'left' => 230,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
        ];
    }

    public static function getDynamic(){
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }
        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg_small.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 364,
                'height' => 364,
                'top' => 5.5,
                'left' => 5.5,
                'file_type' => 'image',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 430,
                'left' => 20,
                'width' => 205,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 120,
                'top' => 380,
                'left' => 230,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
        ];
    }
}