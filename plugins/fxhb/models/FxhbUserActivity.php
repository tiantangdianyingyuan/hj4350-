<?php

namespace app\plugins\fxhb\models;

use app\models\User;
use app\models\UserCoupon;
use Yii;

/**
 * This is the model class for table "{{%fxhb_user_activity}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property int $fxhb_activity_id 活动ID
 * @property int $number 拆包人数
 * @property string $count_price 红包总金额
 * @property string $created_at
 * @property int $is_delete
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $data 活动发起时活动的设置
 * @property int $status 状态0--进行中 1--成功 2--失败
 * @property int $mall_id
 * @property string $token
 * @property int $user_coupon_id
 * @property string $get_price 拆红包获得的金额
 * @property FxhbActivity $activity
 * @property User $user
 * @property UserCoupon $userCoupon
 */
class FxhbUserActivity extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fxhb_user_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'parent_id', 'fxhb_activity_id', 'number', 'created_at', 'is_delete', 'updated_at', 'deleted_at', 'data', 'status', 'mall_id', 'token', 'user_coupon_id'], 'required'],
            [['user_id', 'parent_id', 'fxhb_activity_id', 'number', 'is_delete', 'status', 'mall_id', 'user_coupon_id'], 'integer'],
            [['count_price', 'get_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['data'], 'string'],
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
            'user_id' => 'User ID',
            'parent_id' => 'Parent ID',
            'fxhb_activity_id' => '活动ID',
            'number' => '拆包人数',
            'count_price' => '红包总金额',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'data' => '活动发起时活动的设置',
            'status' => '状态0--进行中 1--成功 2--失败',
            'mall_id' => 'Mall ID',
            'token' => 'Token',
            'user_coupon_id' => 'User Coupon ID',
            'get_price' => '拆红包获得的金额',
        ];
    }

    public function getActivity()
    {
        return $this->hasOne(FxhbActivity::className(), ['id' => 'fxhb_activity_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(FxhbUserActivity::className(), ['parent_id' => 'id']);
    }

    public function getUserCoupon()
    {
        return $this->hasOne(UserCoupon::className(), ['id' => 'user_coupon_id']);
    }
}
