<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%share_cash_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $type 类型 1--收入 2--支出
 * @property string $price 变动佣金
 * @property string $desc
 * @property string $custom_desc
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class ShareCashLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%share_cash_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'type', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['desc', 'custom_desc'], 'string'],
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
            'user_id' => 'User ID',
            'type' => '类型 1--收入 2--支出',
            'price' => '变动佣金',
            'desc' => 'Desc',
            'custom_desc' => 'Custom Desc',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
