<?php

namespace app\plugins\booking\models;

use app\models\ModelActiveRecord;
use app\models\Store;

/**
 * This is the model class for table "{{%booking_store}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $store_id
 * @property int $goods_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class BookingStore extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%booking_store}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'store_id', 'goods_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'store_id', 'goods_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'store_id' => 'Store ID',
            'goods_id' => 'Goods ID',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }
}
