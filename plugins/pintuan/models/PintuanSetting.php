<?php

namespace app\plugins\pintuan\models;

use Yii;

/**
 * This is the model class for table "{{%pintuan_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_share 是否开启分销
 * @property int $is_sms 是否短信提醒
 * @property int $is_mail 是否开启邮件通知
 * @property int $is_print 是否开启订单打印
 * @property int $is_territorial_limitation 区域购买限制
 * @property int $rules 拼团规则
 * @property int $advertisement 拼团广告
 * @property int $is_advertisement 是否开启拼团广告
 * @property string $created_at
 * @property string $payment_type 支付方式
 * @property string $send_type 发货方式
 * @property string $goods_poster 自定义海报
 */
class PintuanSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'rules', 'advertisement', 'created_at', 'payment_type', 'send_type', 'goods_poster'], 'required'],
            [['mall_id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_advertisement'], 'integer'],
            [['rules', 'advertisement', 'payment_type', 'send_type', 'goods_poster'], 'string'],
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
            'is_share' => '是否开启分销',
            'is_sms' => '是否短信提醒',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_territorial_limitation' => '区域购买限制',
            'rules' => '拼团规则',
            'advertisement' => '拼团广告',
            'is_advertisement' => '是否开启拼团广告',
            'created_at' => 'Created At',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'goods_poster' => '自定义海报',
        ];
    }
}
