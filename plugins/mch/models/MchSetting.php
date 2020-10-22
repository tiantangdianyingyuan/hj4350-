<?php

namespace app\plugins\mch\models;

use Yii;

/**
 * This is the model class for table "{{%mch_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $is_share 是否开启分销0.否|1.是
 * @property int $is_sms 是否开启短信提醒
 * @property int $is_mail 是否开启邮件通知
 * @property int $is_print 是否开启订单打印
 * @property int $is_territorial_limitation 区域购买限制
 * @property string $send_type 发货方式
 * @property int $is_web_service
 * @property string $web_service_url;
 * @property string $web_service_pic;
 * @property string $created_at
 */
class MchSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mch_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'send_type', 'created_at'], 'required'],
            [['mall_id', 'mch_id', 'is_share', 'is_sms', 'is_mail', 'is_print',
                'is_territorial_limitation', 'is_web_service'], 'integer'],
            [['send_type'], 'string'],
            [['created_at'], 'safe'],
            [['web_service_url', 'web_service_pic'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'is_share' => '是否开启分销0.否|1.是',
            'is_sms' => '是否开启短信提醒',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_territorial_limitation' => '区域购买限制',
            'send_type' => '发货方式0.快递和自提|1.快递|2.自提',
            'is_web_service' => '客服外链形状',
            'web_service_pic' => '客服外链图标',
            'web_service_url' => '客服外链url',
            'created_at' => 'Created At',
        ];
    }
}
