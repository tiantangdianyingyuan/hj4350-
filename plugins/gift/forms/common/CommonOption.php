<?php

namespace app\plugins\gift\forms\common;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/plugins/default-poster.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 133,
                'height' => 136,
                'top' => 311,
                'left' => 119,
                'file_type' => 'image',
                'pic_url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/plugins/is_big_gift.png',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 90,
                'top' => 140,
                'left' => 142,
                'type' => '1',
                'file_path' => '',
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 255,
                'left' => 128,
                'text' => '小明送你一份礼物',
                'color' => 'rgb(255,255,255)',
                'file_type' => 'text',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 15,
                'top' => 463,
                'left' => 113,
                'width' => 156,
                'text' => '长按识别小程序码抽奖',
                'color' => 'rgb(255,255,255)',
                'file_type' => 'text',
            ],
            'hide_desc' => [
                'is_show' => '1',
                'font' => 14,
                'top' => 487,
                'left' => 113,
                'text' => '7天内无人领取将自动退款',
                'color' => 'rgb(255,255,255)',
                'file_type' => 'text',
            ],
        ];
    }
}