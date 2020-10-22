<?php

namespace app\plugins\miaosha\forms\common;

use app\helpers\PluginHelper;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('miaosha') . '/img/';
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
                'text' => '小红',
                'color' => '#5b85cf',
                'file_type' => 'text',
            ],
            'nickname_share' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 57,
                'left' => 0,
                'text' => '分享给你一个商品',
                'color' => '#353535',
                'file_type' => 'delete',
            ],

            'poster_bg' => [
                'is_show' => '1',
                'width' => 60,
                'height' => 60,
                'top' => 100,
                'left' => 15,
                'file_path' => $iconBaseUrl . 'ms-qrcode.png',
                'file_type' => 'image',
            ],
            'poster_time' => [
                'is_show' => '1',
                'width' => 120,
                'height' => 32,
                'top' => 100,
                'left' => 75,
                'file_path' => $iconBaseUrl . '545.png',
                'file_type' => 'delete',
            ],

            'time_str' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 111,
                'left' => 85,
                'text' => '',
                'color' => '#ffffff',
                'file_type' => 'text',
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
                'font' => 20,
                'top' => 554,
                'left' => 15,
                'color' => '#ff5c5c',
                'text' => '',
                'file_type' => 'text',
            ],
        ];

    }
}