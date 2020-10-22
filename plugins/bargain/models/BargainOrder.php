<?php

namespace app\plugins\bargain\models;

use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%bargain_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $bargain_goods_id 砍价商品id
 * @property string $token
 * @property string $price 售价
 * @property string $min_price 最低价
 * @property int $time 砍价时间
 * @property int $status 状态 0--进行中 1--成功 2--失败
 * @property string $bargain_goods_data 砍价设置
 * @property string $created_at
 * @property int $is_delete
 * @property string $preferential_price 优惠金额
 * @property Goods $goods
 * @property User $user
 * @property BargainUserOrder[] $userOrderList
 * @property int $resetTime
 * @property string $finishAt
 * @property Order $order
 * @property BargainGoods $bargainGoods
 * @property GoodsWarehouse $goodsWarehouse
 */
class BargainOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bargain_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'bargain_goods_id', 'token', 'bargain_goods_data', 'created_at', 'is_delete'], 'required'],
            [['mall_id', 'user_id', 'bargain_goods_id', 'time', 'status', 'is_delete'], 'integer'],
            [['price', 'min_price', 'preferential_price'], 'number'],
            [['bargain_goods_data'], 'string'],
            [['created_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
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
            'user_id' => 'User ID',
            'bargain_goods_id' => '砍价商品id',
            'token' => 'Token',
            'price' => '售价',
            'min_price' => '最低价',
            'time' => '砍价时间',
            'status' => '状态 0--进行中 1--成功 2--失败',
            'bargain_goods_data' => '砍价设置',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'preferential_price' => '优惠金额',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id'])
            ->viaTable(BargainGoods::tableName(), ['id' => 'bargain_goods_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserOrderList()
    {
        return $this->hasMany(BargainUserOrder::className(), ['bargain_order_id' => 'id']);
    }

    // 剩余砍价时间
    public function getResetTime()
    {
        $time = $this->time * 3600 - (time() - strtotime($this->created_at));
        return $time > 0 ? $time : 0;
    }

    // 砍价结束日期
    public function getFinishAt()
    {
        return date('Y-m-d H:i:s', (strtotime($this->created_at) + 3600 * $this->time));
    }

    // 当前价格
    public function getNowPrice($totalPrice)
    {
        $resetPrice = max($this->price - $totalPrice, $this->min_price);
        return price_format($resetPrice, 'float', 2);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['token' => 'token']);
    }

    public function getBargainGoods()
    {
        return $this->hasOne(BargainGoods::className(), ['id' => 'bargain_goods_id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('goods');
    }
}
