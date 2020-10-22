<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_clerk}}".
 *
 * @property int $id
 * @property int $affirm_pay_type 确认收款类型|1.小程序收款|2.后台收款
 * @property int $clerk_type 确认核销类型|1.小程序核销|2.后台核销
 * @property string $clerk_remark 核销备注
 * @property int $mall_id
 * @property int $order_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class OrderClerk extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_clerk}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['affirm_pay_type', 'clerk_type', 'mall_id', 'order_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['clerk_remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'affirm_pay_type' => '确认收款类型',
            'clerk_type' => '核销类型',
            'clerk_remark' => '核销备注',
            'mall_id' => 'Mall ID',
            'order_id' => 'Order ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
