<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 16:22
 */

namespace app\plugins\flash_sale\models;

/**
 * Class Goods
 * @property FlashSaleGoods $flashSaleGoods
 */
class Goods extends \app\models\Goods
{
    public function getFlashSaleGoods()
    {
        return $this->hasOne(FlashSaleGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
