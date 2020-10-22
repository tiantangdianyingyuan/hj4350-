<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pick\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\pick\Plugin;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['goods_id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/pick/goods/goods'
        ];
    }

    public function traitMultiMapContent()
    {
        $plugin = new Plugin();

        $image_url = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/pick-poster-new.png';
        $image = [
            'file_type' => self::TYPE_IMAGE,
            'top' => 0,
            'left' => 0,
            'height' => 110,
            'width' => 120,
            'image_url' => $image_url,
        ];
        return [
            $image,
        ];
    }
}