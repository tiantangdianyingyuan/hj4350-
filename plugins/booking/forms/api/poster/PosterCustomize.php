<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['goods_id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/book/goods/goods',
        ];
    }

    public function traitMultiMapContent()
    {
        $image = [
            'file_type' => self::TYPE_IMAGE,
            'width' => 112,
            'height' => 110,
            'left' => 0,
            'top' => 0,
            'image_url' => PluginHelper::getPluginBaseAssetsUrl('booking') . '/img/booking_qrcode.png',
        ];
        return [$image];
    }
}