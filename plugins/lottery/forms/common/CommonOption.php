<?php

namespace app\plugins\lottery\forms\common;

use app\helpers\PluginHelper;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/';

        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 345,
                'height' => 345,
                'top' => 100,
                'left' => 15,
                'file_type' => 'image',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 34,
                'top' => 44,
                'left' => 15,
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 57,
                'left' => 64,
                'text' => '小明',
                'color' => '#5b85cf',
                'file_type' => 'text',
            ],
            'nickname_share' => [
                'is_show' => '0',
                'font' => 13.5,
                'top' => 57,
                'left' => 0,
                'width' => 375,
                'text' => '分享给你一个商品',
                'color' => '#353535',
                'file_type' => 'text',
            ],

            'poster_bg' => [
                'is_show' => '1',
                'width' => 345,
                'height' => 87,
                'top' => 358,
                'left' => 15,
                'file_path' => $iconBaseUrl . 'qrcode-goods.png',
                'file_type' => 'image',
            ],
            'poster_bg_two' => [
                'is_show' => '1',
                'width' => 60,
                'height' => 28,
                'top' => 544,
                'left' => 15,
                'file_path' => $iconBaseUrl . 'free.png',
                'file_type' => 'image',
            ],

            'qr_code' => [
                'is_show' => '1',
                'size' => 80,
                'top' => 528,
                'left' => 268,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],

            'name' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 467,
                'left' => 15,
                'color' => '#353535',
                'file_type' => 'text',
            ],

            'desc' => [
                'is_show' => '1',
                'font' => 16,
                'top' => 581,
                'left' => 15,
                'width' => 375,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],
            'price' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 554,
                'left' => 91,
                'del_line' => 1,
                'color' => '#999999',
                'text' => '',
                'file_type' => 'text',
            ],
            'line_url' => [
                'is_show' => '1',
                'width' => 0,
                'height' => 1,
                'top' => 563,
                'left' => 90,
                'text' => '',
                'file_path' => $iconBaseUrl . 'line.png',
                'color' => '#999999',
                'file_type' => 'line',
            ]

        ];
    }
}