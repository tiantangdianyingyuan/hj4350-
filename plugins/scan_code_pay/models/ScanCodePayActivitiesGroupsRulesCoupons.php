<?php

namespace app\plugins\scan_code_pay\models;

use app\models\Coupon;
use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities_groups_rules_coupons}}".
 *
 * @property int $id
 * @property int $group_rule_id
 * @property int $coupon_id
 * @property int $send_num 赠送数量
 * @property int $is_delete
 * @property $coupons
 */
class ScanCodePayActivitiesGroupsRulesCoupons extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities_groups_rules_coupons}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_rule_id', 'coupon_id', 'send_num'], 'required'],
            [['group_rule_id', 'coupon_id', 'send_num', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_rule_id' => 'Group Rule ID',
            'coupon_id' => 'Coupon ID',
            'send_num' => '赠送数量',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCoupons()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
