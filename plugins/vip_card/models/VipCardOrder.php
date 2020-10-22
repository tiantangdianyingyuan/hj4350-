<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $user_id
 * @property int $main_id
 * @property string $main_name
 * @property int $detail_id
 * @property int $price
 * @property int $status 0未售 1已售
 * @property string $detail_name
 * @property string $expire 有效期
 * @property string $all_send
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $sign
 */
class VipCardOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'user_id', 'detail_id',], 'required'],
            [['mall_id', 'order_id', 'user_id', 'detail_id', 'status', 'expire', 'main_id'], 'integer'],
            [['price'], 'number'],
            [['all_send', 'detail_name', 'main_name', 'sign'], 'string'],
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
            'order_id' => 'Order ID',
            'user_id' => '用户id',
            'detail_id' => 'Detail ID',
            'status' => 'Status',
            'detail_name' => '子卡名称',
            'expire' => '有效期',
            'all_send' => 'All Send',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'sign' => 'Sign',
        ];
    }

    public function getDetail()
    {
        return $this->hasOne(VipCardDetail::className(), ['id' => 'detail_id']);
    }
}
