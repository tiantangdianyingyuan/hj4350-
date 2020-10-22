<?php

namespace app\plugins\region\models;

/**
 * This is the model class for table "{{%region_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id 代理id
 * @property int $district_id 代理的省市区id
 * @property int $is_update 是否是升级中的关联地区0：否 1：是
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class RegionRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'district_id'], 'required'],
            [['mall_id', 'user_id', 'district_id', 'is_update', 'is_delete'], 'integer'],
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
            'user_id' => '代理id',
            'district_id' => '代理的省市区id',
            'is_update' => 'Is Update',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(RegionUser::class, ['user_id' => 'user_id']);
    }
}
