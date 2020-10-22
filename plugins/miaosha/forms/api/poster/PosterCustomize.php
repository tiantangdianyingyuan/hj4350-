<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\miaosha\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\miaosha\Plugin;


class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/miaosha/goods/goods'
        ];
    }

    public function traitMultiMapContent($class)
    {
        $text = date('m.d', strtotime($class->other->open_date)) . ' ' . $class->other->open_time . ':00场';

        $plugin = new Plugin();
        $mark_image = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/ms-poster-mark.png';
        $image_url = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/ms-poster-c.png';
        $twoImageUrl = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/ms-poster-c-two.png';

        $image = [
            'file_type' => self::TYPE_IMAGE,
            'top' => 0,
            'left' => 0,
            'height' => 110,
            'width' => 120,
            'image_url' => $mark_image,
        ];
        $textBg = [
            'file_type' => self::TYPE_IMAGE,
            'bottom' => 104 - 62,
            'right' => 0,
            'height' => 62,
            'width' => 238,
            'image_url' => $image_url,
            'two_image_url' => $twoImageUrl,
            'two_image_height' => 104
        ];
        $text = [
            'file_type' => self::TYPE_TEXT,
            'bottom' => 104 - 62 + 57,
            'right' => 40,
            'font' => 30,
            'color' => '#ffffff',
            'text' => $text
        ];
        return [
            $image, $textBg, $text
        ];
    }
}