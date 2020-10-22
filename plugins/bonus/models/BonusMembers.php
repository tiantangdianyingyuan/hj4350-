<?php

namespace app\plugins\bonus\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_members}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level 等级
 * @property string $name 等级名称
 * @property int $auto_update 是否开启自动升级
 * @property int $update_type 升级条件类型
 * @property string $update_condition 升级条件
 * @property string $rate 分红比例
 * @property int $status 状态 0--禁用 1--启用
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class BonusMembers extends \app\models\ModelActiveRecord
{
    /** @var string 队长等级升级 */
    const UPDATE_LEVEL = 'bonusCaptainUpdateLevel';

    //分销佣金
    const TOTAL_MONEY = 0;
    //已提现佣金
    const CASHED_MONEY = 1;
    const ALL_MEMBERS = 2;
    const ALL_SHARES = 3;
    const ALL_CAPTAIN = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_members}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level', 'auto_update', 'update_condition', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'level', 'auto_update', 'update_type', 'status', 'is_delete'], 'integer'],
            [['rate'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['update_condition'], 'string', 'max' => 64],
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
            'level' => '等级',
            'name' => '等级名称',
            'auto_update' => '是否开启自动升级',
            'update_type' => '升级条件类型',
            'update_condition' => '升级条件',
            'rate' => '分红比例',
            'status' => '状态 0--禁用 1--启用',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
