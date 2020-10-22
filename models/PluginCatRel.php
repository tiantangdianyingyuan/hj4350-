<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%plugin_cat_rel}}".
 *
 * @property int $id
 * @property string $plugin_name
 * @property string $plugin_cat_name
 */
class PluginCatRel extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_cat_rel}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plugin_name', 'plugin_cat_name'], 'required'],
            [['plugin_name', 'plugin_cat_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plugin_name' => 'Plugin Name',
            'plugin_cat_name' => 'Plugin Cat Name',
        ];
    }
}
