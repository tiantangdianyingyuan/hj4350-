<?php

namespace app\plugins\scan_code_pay\models;

use app\models\GoodsCards;
use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities_groups_rules_cards}}".
 *
 * @property int $id
 * @property int $group_rule_id
 * @property int $card_id
 * @property int $send_num 赠送数量
 * @property int $is_delete
 * @property $cards
 */
class ScanCodePayActivitiesGroupsRulesCards extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities_groups_rules_cards}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_rule_id', 'card_id', 'send_num'], 'required'],
            [['group_rule_id', 'card_id', 'send_num', 'is_delete'], 'integer'],
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
            'card_id' => 'Card ID',
            'send_num' => '赠送数量',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCards()
    {
        return $this->hasOne(GoodsCards::className(), ['id' => 'card_id']);
    }
}
