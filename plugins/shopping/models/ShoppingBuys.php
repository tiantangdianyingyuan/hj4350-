<?php

namespace app\plugins\shopping\models;

use Yii;

/**
 * This is the model class for table "{{%shopping_buys}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $user_id
 * @property int $is_delete
 * @property string $created_at
 */
class ShoppingBuys extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shopping_buys}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'user_id', 'created_at'], 'required'],
            [['mall_id', 'order_id', 'user_id', 'is_delete'], 'integer'],
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
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
        ];
    }
}
