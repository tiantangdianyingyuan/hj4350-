<?php

namespace app\plugins\gift\models;

use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%gift_user_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $gift_id
 * @property int $is_turn 是否转赠0未转1已转
 * @property string $turn_no 转赠码
 * @property int $turn_user_id 被转赠用户ID
 * @property int $is_receive 0未领取，1已领取
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $is_win
 * @property string $token
 */
class GiftUserOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_user_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'gift_id', 'is_turn', 'turn_user_id', 'is_receive', 'is_delete', 'is_win'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['turn_no'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 32],
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
            'gift_id' => 'Gift ID',
            'is_turn' => '是否转赠0未转1已转',
            'turn_no' => '转赠码',
            'turn_user_id' => '被转赠用户ID',
            'is_receive' => '0未领取，1已领取',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_win' => 'Is Win',
            'token' => 'Token'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getGiftLog()
    {
        return $this->hasOne(GiftLog::class, ['id' => 'gift_id']);
    }

    public function getSendUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->viaTable(GiftLog::tableName(), ['id' => 'gift_id']);
    }

    public function getGiftOrder()
    {
        return $this->hasMany(GiftOrder::class, ['user_order_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getParent()
    {
        return $this->hasOne(GiftUserOrder::class, ['turn_user_id' => 'user_id', 'token' => 'token', 'gift_id' => 'gift_id']);
    }

    public function getDetail()
    {
        return $this->hasMany(OrderDetail::class, ['order_id' => 'order_id'])->viaTable(GiftOrder::tableName(), ['user_order_id' => 'id']);
    }

    public function getNotPayOrder()
    {
        return $this->hasOne(Order::class, ['token' => 'token'])->andWhere(['<>', 'cancel_status', 1])->andWhere(['is_pay' => 0]);

    }
}
