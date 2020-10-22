<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/15
 * Time: 14:19
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\models;

/**
 * Class Goods
 * @package app\plugins\bargain\models
 * @property BargainGoods $bargainGoods
 */
class Goods extends \app\models\Goods
{
    public function getBargainGoods()
    {
        return $this->hasOne(BargainGoods::className(), ['goods_id' => 'id']);
    }
}
