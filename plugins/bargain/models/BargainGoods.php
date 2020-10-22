<?php

namespace app\plugins\bargain\models;

use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;

/**
 * This is the model class for table "{{%bargain_goods}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $mall_id
 * @property string $min_price 最低价
 * @property string $begin_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property int $time 砍价小时数
 * @property string $status_data 砍价方式数据
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 * @property int $type 是否允许中途下单 1--允许 2--不允许
 * @property int $stock_type 减库存的方式 1--参与减库存 2--拍下减库存
 * @property int $stock 活动库存
 * @property int $initiator 发起人数
 * @property int $participant 参与人数
 * @property int $min_price_goods 砍到最小价格数
 * @property int $underway 进行中的
 * @property int $success 成功的
 * @property int $fail 失败的
 * @property Goods $goods
 * @property BargainOrder[] $orderList
 * @property GoodsAttr $goodsAttr
 * @property BargainUserOrder[] $userOrderList
 * @property GoodsWarehouse $goodsWarehouse
 */
class BargainGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bargain_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'mall_id', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['goods_id', 'mall_id', 'time', 'is_delete', 'status', 'type', 'stock_type', 'stock', 'initiator',
                'participant', 'min_price_goods', 'underway', 'success', 'fail'], 'integer'],
            [['min_price'], 'number'],
            [['begin_time', 'end_time', 'created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['status_data'], 'string', 'max' => 255],
            [['goods_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'mall_id' => 'Mall ID',
            'min_price' => '最低价',
            'begin_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'time' => '砍价小时数',
            'status_data' => '砍价方式数据',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'type' => '是否允许中途下单 1--允许 2--不允许',
            'stock_type' => '减库存的方式 1--参与减库存 2--拍下减库存',
            'stock' => '活动库存',
            'initiator' => '发起人数',
            'participant' => '参与人数',
            'min_price_goods' => '砍到最小价格数',
            'underway' => '进行中的',
            'success' => '成功的',
            'fail' => '失败的',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getOrderList()
    {
        return $this->hasMany(BargainOrder::className(), ['bargain_goods_id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getUserOrderList()
    {
        return $this->hasMany(BargainUserOrder::className(), ['bargain_order_id' => 'id'])
            ->via('orderList');
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('goods');
    }
}
