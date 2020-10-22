<?php

namespace app\plugins\composition\models;

use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%composition_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $model_id 套餐id
 * @property int $goods_id 商品id
 * @property int $is_host 是否是主商品
 * @property int $is_delete
 * @property string $price 优惠金额
 * @property int $payment_people 支付人数
 * @property int $payment_num 支付件数
 * @property string $payment_amount 支付金额
 * @property string $created_at
 * @property Goods $goods
 */
class CompositionGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%composition_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'model_id', 'goods_id', 'created_at'], 'required'],
            [['mall_id', 'model_id', 'goods_id', 'is_host', 'is_delete', 'payment_people', 'payment_num'], 'integer'],
            [['price', 'payment_amount'], 'number'],
            [['created_at'], 'safe'],
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
            'model_id' => '套餐id',
            'goods_id' => '商品id',
            'is_host' => '是否是主商品',
            'is_delete' => 'Is Delete',
            'price' => '优惠金额',
            'payment_people' => '支付人数',
            'payment_num' => '支付件数',
            'payment_amount' => '支付金额',
            'created_at' => 'Created At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
