<?php

namespace app\plugins\gift\models;

use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%gift_send_order_detail}}".
 *
 * @property int $id
 * @property int $send_order_id
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property string $goods_info 购买商品信息
 * @property int $num
 * @property string $unit_price 商品单价
 * @property string $total_original_price 商品原总价(优惠前)
 * @property string $total_price 商品总价(优惠后)
 * @property string $member_discount_price 会员优惠金额
 * @property int $is_refund 0未退款，1已退款
 * @property int $refund_status 售后状态 0--未售后 1--售后中 2--售后结束
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $receive_num
 * @property string $refund_price
 * @property Goods $goods
 */
class GiftSendOrderDetail extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_send_order_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['send_order_id', 'goods_id', 'num', 'unit_price', 'total_original_price', 'total_price', 'member_discount_price'], 'required'],
            [['send_order_id', 'goods_id', 'goods_attr_id', 'num', 'is_refund', 'refund_status', 'is_delete', 'receive_num'], 'integer'],
            [['goods_info'], 'string'],
            [['unit_price', 'total_original_price', 'total_price', 'refund_price', 'member_discount_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_order_id' => 'Send Order ID',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'goods_info' => '购买商品信息',
            'num' => 'Num',
            'unit_price' => '商品单价',
            'total_original_price' => '商品原总价(优惠前)',
            'total_price' => '商品总价(优惠后)',
            'member_discount_price' => '会员优惠金额',
            'is_refund' => '0未退款，1已退款',
            'refund_status' => '售后状态 0--未售后 1--售后中 2--售后结束',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'receive_num' => '已领取数量',
            'refund_price' => 'Refund Price'
        ];
    }

    /**
     * @param null $goodsInfo
     * @return string
     * @throws \Exception
     */
    public function encodeGoodsInfo($goodsInfo = null)
    {
        if (!$goodsInfo) {
            $goodsInfo = $this->goods_info;
        }
        if (!$goodsInfo) {
            throw new \Exception('goodsInfo不能为空。');
        }
        return Yii::$app->serializer->encode($goodsInfo);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
