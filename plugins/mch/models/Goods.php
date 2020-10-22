<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\models;


/**
 * Class Goods
 * @package app\plugins\mch\models
 * @property MchGoods mchGoods
 * @property Mch mch
 */
class Goods extends \app\models\Goods
{
    public function getMch()
    {
        return $this->hasOne(Mch::className(), ['id' => 'mch_id']);
    }

    public function getMchGoods()
    {
        return $this->hasOne(MchGoods::className(), ['goods_id' => 'id']);
    }
}
