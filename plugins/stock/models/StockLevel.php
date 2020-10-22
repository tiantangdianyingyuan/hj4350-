<?php

namespace app\plugins\stock\models;

use Yii;

/**
 * This is the model class for table "{{%stock_user_grade}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 等级名称
 * @property string $bonus_rate 分红比例
 * @property int $condition 升级条件
 * @property int $is_default 是否默认等级，0否1是
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 */
class StockLevel extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'condition', 'is_default', 'is_delete'], 'integer'],
            [['bonus_rate'], 'number'],
            [['bonus_rate', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
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
            'name' => '等级名称',
            'bonus_rate' => '分红比例',
            'condition' => '升级条件，0不自动升级',
            'is_default' => '是否默认等级，0否1是',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
