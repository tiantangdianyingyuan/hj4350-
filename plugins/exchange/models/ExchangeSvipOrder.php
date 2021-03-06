<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%exchange_svip_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $user_id
 * @property int $code_id
 * @property int $is_delete
 * @property string $created_at
 */
class ExchangeSvipOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_svip_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'user_id', 'code_id'], 'required'],
            [['mall_id', 'order_id', 'user_id', 'code_id', 'is_delete'], 'integer'],
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
            'code_id' => 'Code ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
        ];
    }

    public function getCode()
    {
        return $this->hasOne(ExchangeCode::className(), ['id' => 'code_id']);
    }
}
