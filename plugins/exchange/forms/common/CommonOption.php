<?php

namespace app\plugins\exchange\forms\common;

use app\helpers\PluginHelper;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('exchange') . '/img/';
        $r = 2;
        return [
            'bg_pic' => [
                'url' => $iconBaseUrl . 'exchange-qrcode.png',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 120 / $r,
                'top' => 200 / $r,
                'left' => 55 / $r,
                'file_type' => 'image',
            ],

            'nickname' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 220 / $r,
                'left' => 209 / $r,
                'width' => 726 / $r,
                'text' => '潘先生',
                'color' => '#353535',
                'file_type' => 'text',
            ],

            'qr_code' => [
                'is_show' => '1',
                'size' => 240 / $r,
                'top' => 1020 / $r,
                'left' => 458 / $r,
                'type' => '1',
                'file_path' => '',
                'file_type' => 'image',
            ],

            'exchange_prompt' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 275 / $r,
                'left' => 209 / $r,
                'width' => 616 / $r,
                'text' => '送给你',
                'color' => '#353535',
                'file_type' => 'text',
            ],
            'big_title' => [
                'is_show' => '1',
                'font' => 30,
                'top' => 440 / $r,
                'left' => 158 / $r,
                'width' => 465 / $r,
                'text' => '购物礼品卡',
                'color' => '#d7b983',
                'file_type' => 'text',
            ],

            'small_title' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 540 / $r,
                'left' => 158 / $r,
                'width' => 465 / $r,
                'text' => '送你超值礼品',
                'color' => '#d7b983',
                'file_type' => 'text',
            ],
            'message' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 813 / $r,
                'left' => 71 / $r,
                'width' => 616 / $r,
                'text' => '寄语：一点心意',
                'color' => '#353535',
                'file_type' => 'text',
            ],
            'code' => [
                'is_show' => '1',
                'font' => 24,
                'top' => 1076 / $r,
                'left' => 37 / $r,
                'width' => 350 / $r,
                'text' => 'nOEvUzVq8ELy',
                'color' => '#ff4544',
                'file_type' => 'text',
            ],
            'valid_time' => [
                'is_show' => '1',
                'font' => 16,
                'top' => 1186 / $r,
                'left' => 40 / $r,
                'color' => '#666666',
                'text' => '2020.08.21-2023.09.21',
                'file_type' => 'text',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 13,
                'top' => 1267 / $r,
                'left' => 440 / $r,
                'width' => 312 / $r,
                'text' => '长按识别小程序二维码',
                'color' => '#888888',
                'file_type' => 'text',
            ],
        ];
    }
}
