<?php

namespace app\plugins\stock\models;

use Yii;

/**
 * This is the model class for table "{{%stock_user_grade_up}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type
 * @property string $remark
 */
class StockLevelUp extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_level_up}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type'], 'integer'],
            [['remark'], 'string'],
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
            'type' => '1下线总人数，2累计佣金总额，3已提现佣金总额，4分销订单总数，5分销订单总金额',
            'remark' => 'Remark',
        ];
    }
}
