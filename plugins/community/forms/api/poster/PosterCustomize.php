<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/23
 * Time: 13:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\poster;


use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        $common = CommonMiddleman::getCommon();
        $middlemanId = $common->getQrcodeMiddlemanId(\Yii::$app->user->id);
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id, 'middleman_id' => $middlemanId],
            240,
            'plugins/community/goods/goods',
        ];
    }
}
