<?php

namespace app\plugins\mch\models;

use Yii;

/**
 * This is the model class for table "{{%mch_account_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $money 金额
 * @property string $desc 备注说明
 * @property int $type 类型：1=收入，2=支出
 * @property string $created_at
 */
class MchAccountLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mch_account_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'money', 'desc', 'type', 'created_at'], 'required'],
            [['mall_id', 'mch_id', 'type'], 'integer'],
            [['money'], 'number'],
            [['desc'], 'string'],
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
            'mch_id' => 'Mch ID',
            'money' => '金额',
            'desc' => '备注说明',
            'type' => '类型：1=收入，2=支出',
            'created_at' => 'Created At',
        ];
    }
}
