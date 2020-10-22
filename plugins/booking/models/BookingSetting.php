<?php

namespace app\plugins\booking\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%booking_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_share 是否开启分销
 * @property int $is_sms 是否开启短信通知
 * @property int $is_mail 是否开启邮件通知
 * @property int $is_print 是否开启订单打印
 * @property int $is_cat
 * @property int $is_form
 * @property string $form_data form默认表单
 * @property string $created_at
 * @property string $updated_at
 * @property string $payment_type 支付方式
 * @property string $goods_poster 自定义海报
 */
class BookingSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%booking_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'form_data', 'created_at', 'updated_at', 'payment_type'], 'required'],
            [['mall_id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_cat', 'is_form'], 'integer'],
            [['form_data', 'payment_type', 'goods_poster'], 'string'],
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
            'is_share' => '是否开启分销',
            'is_sms' => '是否开启短信通知',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_cat' => 'Is Cat',
            'form_data' => 'form默认表单',
            'is_form' => '是否默认form',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'payment_type' => '支付方式',
            'goods_poster' => '自定义海报',
        ];
    }
}
