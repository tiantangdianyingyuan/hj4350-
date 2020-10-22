<?php

namespace app\plugins\region\models;

/**
 * This is the model class for table "{{%region_level_up}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $status 0:申请升级中 1:通过升级 2:拒绝升级
 * @property int $level 升级的等级
 * @property string $reason 理由
 * @property int $is_read 0未读 1已读
 * @property int $is_delete
 * @property RegionRelation regionRelation
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class RegionLevelUp extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_level_up}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'level'], 'required'],
            [['mall_id', 'user_id', 'status', 'level', 'is_read', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['reason'], 'string', 'max' => 512],
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
            'user_id' => 'User ID',
            'status' => 'Status',
            'level' => 'Level',
            'reason' => 'Reason',
            'is_read' => 'Is Read',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getRegionRelation()
    {
        return $this->hasMany(RegionRelation::className(), ['user_id' => 'user_id'])->andWhere(['is_update' => 1]);
    }
}
