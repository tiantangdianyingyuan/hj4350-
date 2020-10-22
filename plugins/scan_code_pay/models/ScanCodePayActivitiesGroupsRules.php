<?php

namespace app\plugins\scan_code_pay\models;

use app\models\Coupon;
use app\models\GoodsCards;
use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities_groups_rules}}".
 *
 * @property int $id
 * @property int $group_id
 * @property int $rules_type 1.赠送规则|2.优惠规则
 * @property string $consume_money 单次消费金额
 * @property int $send_integral_num 赠送积分
 * @property int $send_integral_type 1.固定值|2.百分比
 * @property string $send_money 赠送余额
 * @property string $preferential_money 优惠金额
 * @property int $integral_deduction
 * @property int $integral_deduction_type 1.固定值|2.百分比
 * @property int $is_coupon 是否可使用优惠券
 * @property int $is_delete
 * @property $cards;
 * @property $coupons;
 * @property $scanCards;
 * @property $scanCoupons;
 */
class ScanCodePayActivitiesGroupsRules extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities_groups_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'consume_money', 'send_integral_num', 'send_money', 'integral_deduction'], 'required'],
            [['group_id', 'rules_type', 'send_integral_num', 'send_integral_type', 'integral_deduction', 'integral_deduction_type', 'is_coupon', 'is_delete'], 'integer'],
            [['consume_money', 'send_money', 'preferential_money'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'rules_type' => '1.赠送规则|2.优惠规则',
            'consume_money' => '单次消费金额',
            'send_integral_num' => '赠送积分',
            'send_integral_type' => '1.固定值|2.百分比',
            'send_money' => '赠送余额',
            'preferential_money' => '优惠金额',
            'integral_deduction' => 'Integral Deduction',
            'integral_deduction_type' => '1.固定值|2.百分比',
            'is_coupon' => '是否可使用优惠券',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCards()
    {
        return $this->hasMany(GoodsCards::className(), ['id' => 'card_id'])
            ->via('scanCards')->andWhere(['is_delete' => 0]);
    }

    public function getCoupons()
    {
        return $this->hasMany(Coupon::className(), ['id' => 'coupon_id'])
            ->via('scanCoupons')->andWhere(['is_delete' => 0]);

    }

    public function getScanCards()
    {
        return $this->hasMany(ScanCodePayActivitiesGroupsRulesCards::className(), ['group_rule_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getScanCoupons()
    {
        return $this->hasMany(ScanCodePayActivitiesGroupsRulesCoupons::className(), ['group_rule_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
