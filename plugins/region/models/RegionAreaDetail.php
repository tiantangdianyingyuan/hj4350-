<?php

namespace app\plugins\region\models;

/**
 * This is the model class for table "{{%region_area_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $area_id 区域id
 * @property int $province_id 省id
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class RegionAreaDetail extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_area_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'area_id', 'province_id', 'is_delete'], 'integer'],
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
            'area_id' => '区域id',
            'province_id' => '省id',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
