<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_auto_send}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $coupon_id 优惠券
 * @property int $event 触发事件：1=分享，2=购买并付款 3=新人领券
 * @property int $send_count 最多发放次数，0表示不限制
 * @property int $is_delete 删除
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property int $type 领取人 0--所有人 1--指定用户
 * @property string $user_list 指定用户id列表
 * @property Coupon $coupon
 */
class CouponAutoSend extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_auto_send}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'coupon_id', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'coupon_id', 'event', 'send_count', 'is_delete', 'type'], 'integer'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['user_list'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'coupon_id' => '优惠券',
            'event' => '触发事件：1=分享，2=购买并付款',
            'send_count' => '最多发放次数，0表示不限制',
            'is_delete' => '删除',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'type' => '领取人 0--所有人 1--指定用户',
            'user_list' => '指定用户id列表',
        ];
    }

    // 触发事件
    const SHARE = 1;
    const PAY = 2;

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
