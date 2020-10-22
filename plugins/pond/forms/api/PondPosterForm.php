<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\models\UserInfo;

class PondPosterForm extends GrafikaOption
{
    private function getDefault()
    {
        return [
            'pond' => [
                'bg_pic' => [
                    'url' => \Yii::$app->basePath . '/plugins/pond/assets/img/pond-qrcode.png',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 400,
                    'top' => 616,
                    'left' => 175,
                    'type' => '2',
                    'file_path' => '',
                    'file_type' => 'image',
                ],
                'head' => [
                    'is_show' => '1',
                    'size' => 80,
                    'top' => 480,
                    'left' => 160,
                    'file_type' => 'image',
                ],
                'nickname' => [
                    'is_show' => '1',
                    'font' => 25,
                    'top' => 490,
                    'left' => 272,
                    'text' => \Yii::$app->user->identity->nickname,
                    'color' => '#ffffff',
                    'file_type' => 'text',
                ],
                'desc_a' => [
                    'is_show' => '1',
                    'font' => 25,
                    'top' => 530,
                    'left' => 272,
                    'text' => '邀请你一起抽大奖',
                    'color' => '#ffffff',
                    'file_type' => 'text',
                ],
                'desc_b' => [
                    'is_show' => '1',
                    'font' => 30,
                    'top' => 1064,
                    'left' => 270,
                    'text' => '扫描二维码',
                    'color' => '#ffffff',
                    'file_type' => 'text',
                ],
                'desc_c' => [
                    'is_show' => '1',
                    'font' => 30,
                    'top' => 1114,
                    'left' => 240,
                    'text' => '和我一起抽奖',
                    'color' => '#ffffff',
                    'file_type' => 'text',
                ],
            ]
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

    public function get()
    {
        $option = $this->getDefault()['pond'];
        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['user_id' => \Yii::$app->user->id],
            240,
            'plugins/pond/index/index'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }

}
