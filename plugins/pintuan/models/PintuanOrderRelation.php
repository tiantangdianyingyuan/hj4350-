<?php

namespace app\plugins\pintuan\models;

use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%pintuan_order_relation}}".
 *
 * @property int $id
 * @property int $order_id 商城订单ID
 * @property int $user_id 用户ID
 * @property int $is_parent 是否为团长
 * @property int $is_groups 0.单独购买|1.拼团购买
 * @property int $pintuan_order_id 组团订单ID
 * @property string $created_at
 * @property string $is_delete
 * @property int $robot_id
 * @property int $cancel_status
 * @property PintuanOrders $pintuanOrder
 * @property Order $order
 * @property User $user
 * @property PintuanRobots $robot
 */
class PintuanOrderRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_order_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'pintuan_order_id', 'created_at', 'user_id', 'is_groups'], 'required'],
            [['order_id', 'pintuan_order_id', 'is_parent', 'is_delete', 'user_id', 'is_groups', 'robot_id', 'cancel_status'], 'integer'],
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
            'order_id' => '商城订单ID',
            'is_parent' => '是否为团长',
            'is_groups' => '是否为拼团',
            'pintuan_order_id' => '组团订单ID',
            'user_id' => '用户ID',
            'robot_id' => '机器人ID',
            'cancel_status' => '拼团订单取消状态:0.未取消|1.超出拼团总人数取消',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getPintuanOrder()
    {
        return $this->hasOne(PintuanOrders::className(), ['id' => 'pintuan_order_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getRobot()
    {
        return $this->hasOne(PintuanRobots::className(), ['id' => 'robot_id']);
    }
}
