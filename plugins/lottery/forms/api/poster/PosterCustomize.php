<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\lottery\Plugin;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['lottery_id' => $class->other->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/lottery/goods/goods'
        ];
    }

    public function traitPrice($model, $left, $top, $has_center, $color)
    {

        $color = $model->color === '#000000' ? '#d9d9d9' : '#353535';

        $freeUrl = PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/free-p.png';
        $free = $this->setImage($freeUrl, 120, 56, $left, $top);

        $price = '￥' . $model->goods->price;

        $price = $this->setText($price, $left + 120 + 10, $top + 30, 30, $color);

        $font_path = \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';
        $g = imagettfbbox($price['font'], 0, $font_path, $price['text']);
        $g_width = $g[2] - $g[0];

        if ($has_center) {
            $left = 0;
            $left = (750 - $g_width - 120 - 10) / 2;
        }

        $free['left'] = $left;
        $price['left'] = $left + 120 + 10;

        $line = $this->setLine([$left + 120 + 10, $top + 40], [$left + 120 + 10 + $g_width + 10, $top + 40], $color);
        return [
            $free,
            $price,
            $line
        ];

    }

    public function traitMultiMapContent()
    {
        $plugin = new Plugin();
        $twoImageUrl = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/poster-style-two-p.png';
        $image_url = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/qrcode-goods.png';

        $image = [
            'file_type' => self::TYPE_IMAGE,
            'bottom' => 0,
            'left' => 0,
            'height' => 174,
            'width' => "100%",
            'image_url' => $image_url,
            'two_image_url' => $twoImageUrl,
            'two_image_height' => 223
        ];
        return [
            $image,
        ];
    }
}