<?php

namespace app\plugins\pond\models;

use app\models\Goods;
use app\models\ModelActiveRecord;
use app\models\Coupon;

/**
 * This is the model class for table "{{%pond}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 别名
 * @property int $type 1.红包2.优惠券3.积分4.实物.5.无
 * @property string $goods_id 规格
 * @property int $num 积分数量
 * @property string $price 红包价格
 * @property string $image_url 图片
 * @property int $coupon_id 优惠券
 * @property int $stock 库存
 * @property string $created_at
 * @property string $updated_at
 * @property Goods $goods
 */
class Pond extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'type', 'num', 'coupon_id', 'stock', 'goods_id'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'image_url'], 'string', 'max' => 255],
            [['name', 'image_url'], 'default', 'value' => ''],
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
            'name' => '别名',
            'type' => '1.红包2.优惠券3.积分4.实物.5.无',
            'num' => '积分数量',
            'price' => '红包价格',
            'image_url' => '图片',
            'goods_id' => '规格',
            'coupon_id' => '优惠券',
            'stock' => '库存',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
