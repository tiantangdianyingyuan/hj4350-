<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_detail}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $goods_id
 * @property int $num 购买商品数量
 * @property string $unit_price 商品单价
 * @property string $total_original_price 商品原总价(优惠前)
 * @property string $total_price 商品总价(优惠后)
 * @property string $member_discount_price 会员优惠金额(正数表示优惠，负数表示加价)
 * @property string $goods_info 购买商品信息
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_refund 是否退款
 * @property int $refund_status 售后状态 0--未售后 1--售后中 2--售后结束
 * @property string $back_price 后台优惠(正数表示优惠，负数表示加价)
 * @property string $sign 订单详情标识，用于区分插件
 * @property string $goods_no 商品货号
 * @property Goods $goods
 * @property ShareOrder $share
 * @property OrderRefund $refund
 * @property GoodsWarehouse $goodsWarehouse
 * @property Order $order
 * @property string $refundStatusText 售后状态文字
 * @property GoodsCards[] $card
 * @property $userCards
 * @property string $form_data 自定义表单提交的数据
 * @property int $form_id 自定义表单的id
 * @property string $goods_type 商品类型
 * @property OrderDetailExpressRelation $expressRelation
 * @property $goodsCard
 * @property $goodsCoupon
 * @property $coupon
 */
class OrderDetail extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'num', 'unit_price', 'total_original_price', 'total_price', 'member_discount_price', 'goods_info', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['order_id', 'goods_id', 'num', 'is_delete', 'is_refund', 'refund_status', 'form_id'], 'integer'],
            [['unit_price', 'total_original_price', 'total_price', 'member_discount_price', 'back_price'], 'number'],
            [['goods_info', 'form_data'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['sign', 'goods_type'], 'string', 'max' => 255],
            [['goods_no'], 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'num' => '购买商品数量',
            'unit_price' => '商品单价',
            'total_original_price' => '商品原总价(优惠前)',
            'total_price' => '商品总价(优惠后)',
            'member_discount_price' => '会员优惠金额(正数表示优惠，负数表示加价)',
            'goods_info' => '购买商品信息',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_refund' => '是否退款',
            'refund_status' => '售后状态 0--未售后 1--售后中 2--售后结束',
            'back_price' => '后台优惠(正数表示优惠，负数表示加价)',
            'sign' => '订单详情标识，用于区分插件',
            'goods_no' => '商品货号',
            'form_data' => '自定义表单提交的数据',
            'form_id' => '自定义表单的id',
            'goods_type' => '商品类型',
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

    /**
     * @param null $goodsInfo
     * @return \ArrayObject|mixed
     * @throws \Exception
     */
    public function decodeGoodsInfo($goodsInfo = null)
    {
        if (!$goodsInfo) {
            $goodsInfo = $this->goods_info;
        }
        if (!$goodsInfo) {
            throw new \Exception('goodsInfo不能为空。');
        }
        return Yii::$app->serializer->decode($goodsInfo);
    }

    public function getGoodsCard()
    {
        return $this->hasMany(GoodsCardRelation::className(), ['goods_id' => 'goods_id'])->where(['is_delete' => 0]);
    }

    public function getCard()
    {
        return $this->hasMany(GoodsCards::className(), ['id' => 'card_id'])
            ->via('goodsCard');
    }

    public function getGoodsCoupon()
    {
        return $this->hasMany(GoodsCouponRelation::className(), ['goods_id' => 'goods_id'])->where(['is_delete' => 0]);
    }

    public function getCoupon()
    {
        return $this->hasMany(Coupon::className(), ['id' => 'coupon_id'])
            ->via('goodsCoupon');
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getRefund()
    {
        return $this->hasOne(OrderRefund::className(), ['order_detail_id' => 'id'])->andWhere(['is_delete' => 0])->orderBy('created_at DESC');
    }

    public function getUserCards()
    {
        return $this->hasMany(UserCard::className(), ['order_detail_id' => 'id']);
    }

    public function getShare()
    {
        return $this->hasOne(ShareOrder::className(), ['order_detail_id' => 'id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('goods');
    }

    public function getExpressRelation()
    {
        return $this->hasOne(OrderDetailExpressRelation::className(), ['order_detail_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getRefundStatusText()
    {
        if ($this->refund_status == 0) {
            $refundStatusText = '未售后';
        } elseif ($this->refund_status == 1) {
            $refundStatusText = '售后申请中';
        } elseif ($this->refund_status == 2) {
            $refundStatusText = '售后完成';
        } else {
            $refundStatusText = '位置状态';
        }
        return $refundStatusText;
    }

    public function getOrderRefund()
    {
        return $this->hasOne(OrderRefund::className(), ['order_detail_id' => 'id']);
    }

    public function changePluginData($pluginData)
    {
        if (version_compare(\Yii::$app->getAppVersion(), '4.2.88') == 1) {
            return $pluginData;
        } else {
            if (isset($pluginData['discount_list'])) {
                return $pluginData['discount_list'];
            } else {
                return [];
            }
        }
    }
}
