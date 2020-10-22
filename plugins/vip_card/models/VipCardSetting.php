<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_vip_card
 * @property string $payment_type
 * @property int $is_share 是否开启分销
 * @property int $is_sms
 * @property int $is_mail
 * @property int $is_agreement
 * @property string $agreement_title
 * @property string $agreement_content
 * @property int $is_buy_become_share 购买成为分销商 购买成为分销商 0:关闭 1开启
 * @property int $share_type 1.百分比|2.固定金额
 * @property string $share_commission_first
 * @property string $share_commission_second
 * @property string $share_commission_third
 * @property string $rules
 * @property string $form
 * @property int $is_order_form 下单表单开关
 * @property string $order_form
 * @property string $share_level
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class VipCardSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'payment_type', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_vip_card', 'is_share', 'is_sms', 'is_mail', 'is_agreement', 'share_type', 'is_order_form', 'is_delete', 'is_buy_become_share'], 'integer'],
            [['payment_type', 'agreement_content', 'form', 'order_form', 'share_level'], 'string'],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number'],
            [['created_at', 'updated_at', 'deleted_at', 'rules'], 'safe'],
            [['agreement_title'], 'string', 'max' => 255],
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
            'is_vip_card' => 'Is Vip Card',
            'payment_type' => 'Payment Type',
            'form' => '自定义表单',
            'rules' => '插件规则',
            'is_share' => '是否开启分销',
            'is_sms' => 'Is Sms',
            'is_mail' => 'Is Mail',
            'is_agreement' => 'Is Agreement',
            'agreement_title' => 'Agreement Title',
            'agreement_content' => 'Agreement Content',
            'share_type' => '1.百分比|2.固定金额',
            'share_commission_first' => 'Share Commission First',
            'share_commission_second' => 'Share Commission Second',
            'share_commission_third' => 'Share Commission Third',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_buy_become_share' => '购买成为分销商',
            'share_level' => '分销等级',
            'is_order_form' => 'Is Order Form',
            'order_form' => 'Order Form',
        ];
    }
}
