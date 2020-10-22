<?php

namespace app\plugins\miaosha\models;

use Yii;

/**
 * This is the model class for table "{{%miaosha_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $over_time 未支付订单取消时间
 * @property int $is_share 是否开启分销
 * @property int $is_sms 是否短信提醒
 * @property int $is_mail 是否开启邮件通知
 * @property int $is_print 是否开启订单打印
 * @property int $is_territorial_limitation 区域购买限制
 * @property string $created_at
 * @property string $open_time 秒杀开放时间
 * @property string $payment_type
 * @property string $send_type
 * @property string $goods_poster
 */
class MiaoshaSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%miaosha_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'open_time', 'created_at', 'payment_type', 'send_type', 'goods_poster'], 'required'],
            [['mall_id', 'over_time', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['open_time', 'payment_type', 'send_type', 'goods_poster'], 'string'],
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
            'mall_id' => 'Mall ID',
            'over_time' => '未支付订单取消时间',
            'is_share' => '是否开启分销',
            'is_sms' => '是否短信提醒',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_territorial_limitation' => '区域购买限制',
            'created_at' => 'Created At',
            'open_time' => '秒杀开放时间',
            'payment_type' => 'Payment Type',
            'send_type' => 'Send Type',
        ];
    }
}
