<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%exchange_code_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $is_success 是否兑换成功
 * @property string $code
 * @property string $origin admin app
 * @property string $remake 简单说明
 * @property string $created_at
 */
class ExchangeCodeLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_code_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'origin'], 'required'],
            [['mall_id', 'user_id', 'is_success'], 'integer'],
            [['created_at'], 'safe'],
            [['remake'], 'string'],
            [['code', 'remake'], 'string', 'max' => 255],
            [['origin'], 'string', 'max' => 100],
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
            'is_success' => '是否兑换成功',
            'code' => 'Code',
            'origin' => 'admin app',
            'remake' => '简单说明',
            'created_at' => 'Created At',
        ];
    }
}
