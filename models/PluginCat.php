<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%plugin_cat}}".
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $color
 * @property int $sort
 * @property string $icon
 * @property int $is_delete
 * @property string $add_time
 * @property string $update_time
 */
class PluginCat extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_cat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'display_name'], 'required'],
            [['sort', 'is_delete'], 'integer'],
            [['icon'], 'string'],
            [['add_time', 'update_time'], 'safe'],
            [['name', 'display_name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 24],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'display_name' => 'Display Name',
            'sort' => 'Sort',
            'icon' => 'Icon',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getPlugins()
    {
        return $this->hasMany(CorePlugin::class, [
            'name' => 'plugin_name'
        ])->via('pluginCatRels');
    }

    public function getPluginCatRels()
    {
        return $this->hasMany(PluginCatRel::class, [
            'plugin_cat_name' => 'name',
        ]);
    }
}
