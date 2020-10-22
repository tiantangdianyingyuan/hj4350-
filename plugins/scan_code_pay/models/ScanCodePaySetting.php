<?php

namespace app\plugins\scan_code_pay\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_scan_code_pay
 * @property string $payment_type
 * @property int $is_share 是否开启分销
 * @property int $is_sms 是否短信提醒
 * @property int $is_mail 是否开启邮件提醒
 * @property int $share_type 1.百分比|2.固定金额
 * @property string $share_commission_first
 * @property string $share_commission_second
 * @property string $share_commission_third
 * @property string $poster 自定义海报
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class ScanCodePaySetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'payment_type', 'poster', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_scan_code_pay', 'is_share', 'share_type', 'is_delete', 'is_sms', 'is_mail'], 'integer'],
            [['payment_type', 'poster'], 'string'],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number'],
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
            'is_scan_code_pay' => 'Is Scan Code Pay',
            'payment_type' => 'Payment Type',
            'is_share' => '是否开启分销',
            'is_sms' => '是否短信提醒',
            'is_mail' => '是否开启邮件提醒',
            'share_type' => '1.百分比|2.固定金额',
            'share_commission_first' => 'Share Commission First',
            'share_commission_second' => 'Share Commission Second',
            'share_commission_third' => 'Share Commission Third',
            'poster' => '自定义海报',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
