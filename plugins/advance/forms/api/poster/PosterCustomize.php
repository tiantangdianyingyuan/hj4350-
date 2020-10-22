<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\advance\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\advance\Plugin;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/advance/detail/detail',
        ];
    }

    public function traitMultiMapContent()
    {
        $plugin = new Plugin();
        $twoImageUrl = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/poster-style-two.png';
        $image_url = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/poster-bg.png';

        $image = [
            'file_type' => self::TYPE_IMAGE,
            'bottom' => 0,
            'right' => 0,
            'height' => 120,
            'width' => 300,
            'image_url' => $image_url,
            'two_image_url' => $twoImageUrl,
            'two_image_height' => 152
        ];
        return [
            $image,
        ];
    }
}