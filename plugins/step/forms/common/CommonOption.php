<?php

namespace app\plugins\step\forms\common;

use app\helpers\PluginHelper;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('step') . '/img/';

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
                'text' => '分享给你一个商品',
                'color' => '#353535',
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
            'poster_bg' => [
                'is_show' => '0',
                'width' => 60,
                'height' => 60,
                'top' => 100,
                'left' => 15,
                'file_path' => '',
                'file_type' => 'image',
            ],
            'name' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 467,
                'left' => 15,
                'color' => '#353535',
                'text' => '',
                'file_type' => 'text',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 16,
                'top' => 581,
                'left' => 15,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'width' => 375,
                'file_type' => 'text',
            ],
            'price' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 554,
                'left' => 15,
                'color' => '#ff5c5c',
                'text' => '5活力币+￥12',
                'file_type' => 'text',
            ],
        ];
    }

    public static function getStepPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('step') . '/img/';

        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 375,
                'height' => 450,
                'top' => 0,
                'left' => 0,
                'file_type' => 'image',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 34,
                'top' => 475,
                'left' => 149,
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 480,
                'left' => 199,
                'text' => '小红',
                'color' => '#5b85cf',
                'file_type' => 'text',
            ],

            'qr_code' => [
                'is_show' => '1',
                'size' => 80,
                'top' => 474,
                'left' => 22,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
            'poster_bg' => [
                'is_show' => '0',
                'width' => 60,
                'height' => 60,
                'top' => 100,
                'left' => 15,
                'file_path' => '',
                'file_type' => 'image',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 14,
                'top' => 546,
                'left' => 149,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'width' => 375,
                'file_type' => 'text',
            ],
            'name' => [
                'is_show' => '1',
                'font' => 17,
                'top' => 497,
                'left' => 199,
                'color' => '#353535',
                'text' => '已走了1000步',
                'file_type' => 'text',
            ],
            'run_text' => [
                'is_show' => '0',
                'font' => 25,
                'top' => 1040,
                'left' => 298,
                'color' => '#353535',
                'text' => '走路还能赚钱',
                'file_type' => 'text',
            ]
        ];
    }
}