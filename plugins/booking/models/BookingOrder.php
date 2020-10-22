<?php

namespace app\plugins\booking\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%booking_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $token
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class BookingOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%booking_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'token', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'token' => 'Token',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
