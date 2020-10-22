<?php

namespace app\plugins\gift\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%gift_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $num 礼物总数
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $is_pay
 * @property int $is_confirm 送礼状态：0.未完成送礼|1.已完成送礼
 * @property string $type 送礼方式
 * @property string $open_time 开奖时间
 * @property int $open_num 开奖所需人数
 * @property int $open_type 0一人拿奖，1多人各领一份奖
 * @property string $bless_word 祝福语
 * @property string $bless_music 祝福语音
 * @property string $auto_refund_time 自动退款时间
 * @property int $order_id
 * @property int $is_cancel
 * @property GiftUserOrder $userOrder
 */
class GiftLog extends \app\models\ModelActiveRecord
{
    /** @var string 订单取消 */
    const EVENT_CANCELED = 'orderCanceled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'num', 'is_delete', 'is_pay', 'is_confirm', 'open_num', 'open_type', 'order_id', 'is_cancel'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'open_time', 'auto_refund_time'], 'safe'],
            [['type', 'bless_word', 'auto_refund_time'], 'required'],
            [['type'], 'string', 'max' => 60],
            [['bless_word', 'bless_music'], 'string', 'max' => 200],
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
            'num' => '礼物总数',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_confirm' => '送礼状态：0.未完成送礼|1.已完成送礼',
            'type' => '送礼方式',
            'open_time' => '开奖时间',
            'open_num' => '开奖所需人数',
            'open_type' => '0一人拿奖，1多人各领一份奖',
            'bless_word' => '祝福语',
            'bless_music' => '祝福语音',
            'auto_refund_time' => '自动退款时间',
            'is_pay' => 'Is Pay',
            'order_id' => 'Order ID',
            'is_cancel' => 'Is Cancel'
        ];
    }

    public function getUserOrder()
    {
        return $this->hasMany(GiftUserOrder::class, ['gift_id' => 'id'])->where(['is_turn' => 0]);
    }

    public function getWinUser()
    {
        return $this->hasMany(GiftUserOrder::class, ['gift_id' => 'id'])->where(['is_win' => 1, 'is_turn' => 0]);
    }

    public function getSendOrder()
    {
        return $this->hasMany(GiftSendOrder::class, ['gift_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getGiftOrderNum()
    {
        return $this->hasMany(GiftOrder::class, ['user_order_id' => 'id'])->andWhere(['>', 'order_id', 0])
            ->viaTable(GiftUserOrder::tableName(), ['gift_id' => 'id']);
    }
}
