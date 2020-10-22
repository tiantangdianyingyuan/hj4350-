<?php

namespace app\plugins\scratch\models;

use app\models\Goods;
use app\models\ModelActiveRecord;
use app\models\Coupon;

/**
 * This is the model class for table "{{%scratch}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type 1.红包2.优惠券3.积分4.实物.5.无
 * @property int $status 状态 0 关闭 1开启
 * @property string $goods_id 商品
 * @property int $num 积分数量
 * @property string $price 红包价格
 * @property int $coupon_id 优惠券
 * @property int $stock 库存
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Goods $goods
 */
class Scratch extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scratch}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'status', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'type', 'status', 'num', 'coupon_id', 'stock', 'is_delete', 'goods_id'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['stock', 'coupon_id', 'price', 'num', 'goods_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'type' => '1.红包2.优惠券3.积分4.实物.5.无',
            'status' => '状态 0 关闭 1开启',
            'goods_id' => '商品',
            'num' => '积分数量',
            'price' => '红包价格',
            'coupon_id' => '优惠券',
            'stock' => '库存',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
