<?php

namespace app\plugins\bonus\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_order_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id 订单ID
 * @property int $from_user_id 下单用户ID
 * @property int $to_user_id 受益用户ID
 * @property string $price 订单商品实付金额
 * @property string $bonus_price 分红金额
 * @property string $fail_bonus_price 失败分红金额
 * @property int $status 0预计分红，1完成分红，2分红失败
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $remark 备注
 * @property int $bonus_rate 下单时的分红比例%
 */
class BonusOrderLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_order_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'from_user_id', 'to_user_id', 'status', 'is_delete'], 'integer'],
            [['price', 'bonus_price', 'fail_bonus_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['remark'], 'string', 'max' => 200],
            [['bonus_rate'], 'string', 'max' => 32],
            [['order_id'], 'unique'],
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
            'order_id' => '订单ID',
            'from_user_id' => '下单用户ID',
            'to_user_id' => '受益用户ID',
            'price' => '订单商品实付金额',
            'bonus_price' => '分红金额',
            'fail_bonus_price' => '失败分红金额',
            'status' => '0预计分红，1完成分红，2分红失败',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'remark' => '备注',
            'bonus_rate' => '下单时的分红比例%',
        ];
    }
}
