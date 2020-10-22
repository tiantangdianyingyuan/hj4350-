<?php

namespace app\plugins\gift\models;

use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Order;
use Yii;

/**
 * This is the model class for table "{{%gift_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $order_no
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $num
 * @property int $order_id 商城订单ID
 * @property string $type 送礼方式
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $user_order_id
 * @property int $is_refund
 * @property int $buy_order_detail_id 买礼物的商城订单详情id
 * @property int $gift_id 礼物id
 */
class GiftOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'goods_attr_id', 'num', 'order_id', 'is_delete', 'user_order_id', 'is_refund', 'gift_id',
                'buy_order_detail_id'], 'integer'],
            [['type'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['order_no'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 60],
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
            'order_no' => 'Order No',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'num' => 'Num',
            'order_id' => '商城订单ID',
            'type' => '送礼方式',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'user_order_id' => 'User Order ID',
            'is_refund' => '退款',
            'buy_order_detail_id' => '买礼物的商城订单详情id',
            'gift_id' => '礼物id',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'goods_id']);
    }

    public function getUserOrder()
    {
        return $this->hasOne(GiftUserOrder::class, ['id' => 'user_order_id']);
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(GoodsAttr::class, ['id' => 'goods_attr_id']);
    }
}
