<?php

namespace app\plugins\bargain\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%bargain_user_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $bargain_order_id 砍价订单ID
 * @property string $price 砍价的金额
 * @property int $is_delete
 * @property string $created_at
 * @property string $token
 * @property User $user
 */
class BargainUserOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bargain_user_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'bargain_order_id', 'is_delete', 'created_at', 'token'], 'required'],
            [['mall_id', 'user_id', 'bargain_order_id', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['created_at'], 'safe'],
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
            'bargain_order_id' => '砍价订单ID',
            'price' => '砍价的金额',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'token' => 'Token',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
