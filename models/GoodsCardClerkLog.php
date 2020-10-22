<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_card_clerk_log}}".
 *
 * @property int $id
 * @property int $user_card_id 卡券ID
 * @property int $clerk_id 核销员ID
 * @property int $store_id 核销门店ID
 * @property int $use_number 核销次数
 * @property int $surplus_number 剩余次数
 * @property string $clerked_at 核销时间
 * @property Store $store
 * @property User $user
 */
class GoodsCardClerkLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_card_clerk_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_card_id', 'clerk_id', 'store_id', 'use_number', 'clerked_at', 'surplus_number'], 'required'],
            [['user_card_id', 'clerk_id', 'store_id', 'use_number', 'surplus_number'], 'integer'],
            [['clerked_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_card_id' => '用户卡券ID',
            'clerk_id' => '核销员ID',
            'store_id' => '核销门店ID',
            'use_number' => '核销次数',
            'surplus_number' => '剩余次数',
            'clerked_at' => '核销时间',
        ];
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'clerk_id']);
    }
}
