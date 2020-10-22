<?php

namespace app\plugins\booking\models;

use app\models\ModelActiveRecord;
use app\models\Store;

/**
 * This is the model class for table "{{%booking_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property string $form_data 自定义表单
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_order_form
 * @property int $order_form_type
 * @property Goods $goods
 */
class BookingGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%booking_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'form_data', 'created_at', 'updated_at', 'deleted_at', 'is_order_form'], 'required'],
            [['mall_id', 'is_delete', 'goods_id', 'is_order_form', 'order_form_type', 'order_form_type'], 'integer'],
            [['form_data'], 'string'],
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
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'form_data' => '自定义表单',
            'is_delete' => '删除',
            'is_order_form' => '是否开启表单',
            'order_form_type' => '表单类型',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getStore()
    {
        return $this->hasMany(Store::className(), ['id' => 'store_id'])
            ->viaTable(BookingStore::tableName(), ['goods_id' => 'goods_id', 'is_delete' => 'is_delete']);
    }

    public function getCurrentStore()
    {
        return $this->hasMany(Store::className(), ['id' => 'store_id', 'is_delete' => 'is_delete'])
            ->viaTable(BookingStore::tableName(), ['goods_id' => 'goods_id', 'is_delete' => 'is_delete']);
    }
}
