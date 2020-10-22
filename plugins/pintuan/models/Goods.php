<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\models;


/**
 * Class Goods
 * @package app\plugins\pintuan\models
 * @property PintuanGoodsGroups $groups
 * @property PintuanGoodsGroups $oneGroups
 * @property PintuanGoods $pintuanGoods
 * @property PintuanOrders[] $pintuanOrder
 */
class Goods extends \app\models\Goods
{
    public function getGroups()
    {
        return $this->hasMany(PintuanGoodsGroups::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getOneGroups()
    {
        return $this->hasOne(PintuanGoodsGroups::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getPintuanGoods()
    {
        return $this->hasOne(PintuanGoods::className(), ['goods_id' => 'id']);
    }

    public function getPintuanOrder()
    {
        return $this->hasMany(PintuanOrders::className(), ['goods_id' => 'id']);
    }

    /**
     * @param Goods $goods
     * @return string
     */
    public function getActivityStatus($goods)
    {
        $status = '未知';
        $defaultTime = '0000-00-00 00:00:00';
        try {
            $todayDate = date('Y-m-d H:i:s');
            $ptGoods = $goods->pintuanGoods;
            if ($ptGoods->start_time > $todayDate) {
                $status = '未开始';
            } elseif ($ptGoods->start_time <= $todayDate && $ptGoods->end_time >= $todayDate || $ptGoods->end_time == $defaultTime) {
                $status = '进行中';
            } elseif ($ptGoods->end_time < $todayDate) {
                $status = '已结束';
            }

            if ($goods->status == 0) {
                $status = '下架中';
            }
        } catch (\Exception $exception) {
        }

        return $status;
    }

    /**
     * @param Goods $goods
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getGoodsGroups($goods)
    {
        $ptGoodsId = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goods->id])
            ->select('id');
        $goodsIds = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'pintuan_goods_id' => $ptGoodsId])
            ->select('goods_id');
        $goodsList = Goods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsIds])
            ->with('oneGroups')->all();

        return $goodsList;
    }
}
