<?php

namespace app\plugins\integral_mall\models;

use Yii;

/**
 * This is the model class for table "{{%integral_mall_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_share
 * @property int $is_sms
 * @property int $is_mail
 * @property int $is_print
 * @property int $is_territorial_limitation
 * @property string $desc 积分说明
 * @property string $created_at
 * @property string $updated_at
 * @property string $payment_type 支付方式
 * @property string $send_type 发货方式
 * @property string $goods_poster 自定义海报
 */
class IntegralMallSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'desc', 'created_at', 'updated_at', 'payment_type', 'send_type', 'goods_poster'], 'required'],
            [['mall_id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['desc', 'payment_type', 'send_type', 'goods_poster'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
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
            'is_share' => 'Is Share',
            'is_sms' => 'Is Sms',
            'is_mail' => 'Is Mail',
            'is_print' => 'Is Print',
            'is_territorial_limitation' => 'Is Territorial Limitation',
            'desc' => '积分说明',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'goods_poster' => '自定义海报'
        ];
    }
}
