<?php

namespace app\plugins\advance\models;

use Yii;

/**
 * This is the model class for table "{{%advance_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_advance
 * @property string $payment_type
 * @property string $deposit_payment_type
 * @property int $is_share 是否开启分销
 * @property int $is_sms
 * @property int $is_mail
 * @property int $is_print
 * @property int $is_territorial_limitation 是否开启区域允许购买
 * @property string $goods_poster
 * @property string $send_type 发货方式
 * @property int $over_time 未支付定金订单超时时间
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class AdvanceSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'payment_type', 'goods_poster', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_advance', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'over_time', 'is_delete'], 'integer'],
            [['payment_type', 'goods_poster', 'deposit_payment_type'], 'string'],
            [['send_type'], 'string', 'max' => 255],
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
            'is_advance' => 'Is Advance',
            'payment_type' => 'Payment Type',
            'deposit_payment_type' => '定金支付类型',
            'is_share' => 'Is Share',
            'is_sms' => 'Is Sms',
            'is_mail' => 'Is Mail',
            'is_print' => 'Is Print',
            'is_territorial_limitation' => 'Is Territorial Limitation',
            'goods_poster' => 'Goods Poster',
            'send_type' => 'Send Type',
            'over_time' => 'Over Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
