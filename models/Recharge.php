<?php

namespace app\models;

/**
 * This is the model class for table "{{%recharge}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $pay_price 支付价格
 * @property string $send_price 赠送价格
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $send_integral 赠送的积分
 * @property int $send_member_id 赠送的会员
 */
class Recharge extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'pay_price', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'send_integral', 'send_member_id'], 'integer'],
            [['pay_price', 'send_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => '名称',
            'pay_price' => '支付价格',
            'send_price' => '赠送价格',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Update At',
            'deleted_at' => 'Deleted At',
            'send_integral' => '赠送的积分',
            'send_member_id' => '赠送的会员',
        ];
    }

    public function getMember()
    {
        return $this->hasOne(MallMembers::className(), ['id' => 'send_member_id', 'is_delete' => 'is_delete']);
    }
}
