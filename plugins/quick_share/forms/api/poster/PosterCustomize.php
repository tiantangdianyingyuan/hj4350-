<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\models\Model;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'pages/goods/goods'
        ];
    }

    public function traitMultiMap($class)
    {
        $pic_list = $class->other ?: $class->goods->goodsWarehouse->pic_url;
        $pic_list = \yii\helpers\BaseJson::decode($pic_list);
        $pic_list = array_column($pic_list, 'pic_url');

        return $pic_list;
    }
}