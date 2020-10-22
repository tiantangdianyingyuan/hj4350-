<?php

namespace app\plugins\vip_card\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\helpers\PluginHelper;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\models\VipCard;

class PosterForm extends GrafikaOption
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
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

    private function caleWidth($fontSize, $title, $left)
    {
        $label = imagettfbbox($fontSize, 0, $this->font_path, $title);
        $label_width = $label[2] - $label[0];
        return $left + $label_width;
    }

    private function get()
    {
        /** @var $card VipCard */
        $card = CommonVip::getCommon()->getMainCard();
        $option = $this->default();
        $card = \yii\helpers\ArrayHelper::toArray($card);
        if (empty($card)) {
            throw new \Exception('数据未配置');
        }
        extract($card);

        $setting = (new CommonVipCardSetting())->getSetting();
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        $head_card = trim($setting['form']['head_card'] ?? '');
        if (substr($head_card, 0, 4) === 'http') {
            $option['boxBg']['file_path'] = $head_card;
        } elseif ($head_card) {
            $option['boxBg']['file_path'] = \Yii::$app->basePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $head_card;
        }

        $type = json_decode($type_info, true);
        $option['box_name']['text'] = self::autowrap($option['box_name']['font'], 0, $this->font_path, $name, $option['box_name']['width'], 4);
        $option['share_text']['text'] = self::autowrap($option['share_text']['font'], 0, $this->font_path, sprintf('分享%s', $name), $option['share_text']['width'], 1);
        $option['share_right']['left'] = $this->caleWidth(
            $option['share_text']['font'],
            $option['share_text']['text'],
            $option['share_text']['left'] + 3
        );
        $option['share_center']['width'] = $option['share_right']['left'] - $option['share_text']['left'];
        if ($type['all'] == true) {
            $title = '全场自营商品';
        } else {
            $title = '指定分类/商品';
        }
        $option['discount_label']['text'] = $title;
        if (floatval($discount) === floatval(0)) {
            $discount = '免费';
            $option['discount_num']['top'] = $option['discount_label']['top'];
            $option['discount_num']['font'] = $option['discount_label']['font'];
        }

        $option['discount_num']['text'] = $discount;

        $option['discount_num']['left'] = $this->caleWidth(
            $option['discount_label']['font'],
            $title,
            $option['discount_label']['left'] + 3
        );
        $option['discount_zhe']['left'] = $this->caleWidth(
            $option['discount_num']['font'],
            $discount,
            $option['discount_num']['left'] + 8
        );

        if ($is_free_delivery == 1) {
            $line = 45;
            $option['discount_icon']['top'] -= $line;
            $option['discount_label']['top'] -= $line;
            $option['discount_num']['top'] -= $line;
            $option['discount_zhe']['top'] -= $line;
            $option['delivery_icon']['top'] += $line;
            $option['delivery_label']['top'] += $line;
        } else {
            unset($option['delivery_icon']);
            unset($option['delivery_label']);
        }

        if (floatval($discount) === floatval(0)) {
            unset($option['discount_zhe']);
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['user_id' => \Yii::$app->user->id],
            240,
            'plugins/vip_card/index/index'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }

    private function default()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('vip_card') . '/img/poster/';

        return [
            'bg_pic' => [
                'url' => $iconBaseUrl . 'poster_bg.png',
            ],
            'user_bg' => [
                'is_show' => '1',
                'width' => 113,
                'height' => 120,
                'top' => 17,
                'left' => 40,
                'file_path' => $iconBaseUrl . 'user_bg.png',
                'file_type' => 'image',
            ],
            'head' => [
                'is_show' => '1',
                'width' => 92,
                'height' => 92,
                'top' => 40,
                'left' => 56,
                'file_path' => $iconBaseUrl . 'user_bg.png',
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 28 / 1.48,
                'top' => 45,
                'left' => 40 + 113 + 24,
                'text' => \Yii::$app->user->identity->nickname,
                'color' => '#ffffff',
                'file_type' => 'text',
            ],
            ///////////////////////////////
            'share_left' => [
                'is_show' => '1',
                'width' => 23,
                'height' => 50,
                'top' => 80,
                'left' => 40 + 113 + 24,
                'file_path' => $iconBaseUrl . 'share-left.png',
                'file_type' => 'image',
            ],
            'share_center' => [
                'is_show' => '1',
                'width' => 0,
                'height' => 50,
                'top' => 80,
                'left' => 40 + 113 + 24 + 23,
                'file_path' => $iconBaseUrl . 'share-center.png',
                'file_type' => 'image',
            ],
            'share_right' => [
                'is_show' => '1',
                'width' => 24,
                'height' => 50,
                'top' => 80,
                'left' => 40 + 113 + 24 + 23,
                'file_path' => $iconBaseUrl . 'share-right.png',
                'file_type' => 'image',
            ],
            'share_text' => [
                'is_show' => '1',
                'font' => 24 / 1.48,
                'top' => 95,
                'left' => 40 + 113 + 24 + 23,
                'text' => '',
                'width' => 416,
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            ///////////////////////////////
            'boxBg' => [
                'is_show' => '1',
                'width' => 490,
                'height' => 251,
                'top' => 171,
                'left' => 130,
                'file_path' => $iconBaseUrl . 'box-bg.png',
                'file_type' => 'image',
            ],
            'box_name' => [
                'is_show' => '1',
                'font' => 32 / 1.48,
                'top' => 171 + 40,
                'left' => 130 + 41,
                'text' => '',
                'width' => 490 - 40 - 40,
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            //////////////////////////
            'discount_icon' => [
                'is_show' => '1',
                'width' => 66,
                'height' => 66,
                'top' => 626,
                'left' => 150,//////
                'file_path' => $iconBaseUrl . 'icon_sale.png',
                'file_type' => 'image',
            ],
            'discount_label' => [
                'is_show' => '1',
                'font' => 28 / 1.48,
                'top' => 650,
                'left' => 230, ////
                'text' => '',
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            'discount_num' => [
                'is_show' => '1',
                'font' => 50 / 1.48,
                'top' => 636,
                'left' => 0,
                'text' => '',
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            'discount_zhe' => [
                'is_show' => '1',
                'font' => 28 / 1.48,
                'top' => 650,
                'left' => 0,
                'text' => '折',
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            //////////////////
            'delivery_icon' => [
                'is_show' => '1',
                'width' => 66,
                'height' => 66,
                'top' => 626,
                'left' => 150,//////
                'file_path' => $iconBaseUrl . 'icon_freeshipping.png',
                'file_type' => 'image',
            ],
            'delivery_label' => [
                'is_show' => '1',
                'font' => 28 / 1.48,
                'top' => 650,
                'left' => 230, ////
                'text' => '自营商品包邮',
                'color' => '#342e25',
                'file_type' => 'text',
            ],
            //////////////////
            'qr_code' => [
                'is_show' => '1',
                'size' => 240,
                'top' => 1003,
                'left' => 255,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],
            'desc' => [
                'is_show' => '1',
                'width' => 300,
                'height' => 50,
                'top' => 1003 + 240 + 20,
                'left' => 225,
                'file_path' => $iconBaseUrl . 'desc.png',
                'file_type' => 'image',
            ],
        ];
    }
}
