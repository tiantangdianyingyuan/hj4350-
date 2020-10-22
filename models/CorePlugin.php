<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%core_plugin}}".
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $version
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $pic_url
 * @property string $desc
 * @property int $sort
 */
class CorePlugin extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_plugin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'display_name'], 'required'],
            [['is_delete', 'sort'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['pic_url', 'desc'], 'string'],
            [['name', 'display_name', 'version'], 'string', 'max' => 64],
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
            'version' => 'Version',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'pic_url' => 'Pic Url',
            'desc' => 'Desc',
            'sort' => 'Sort',
        ];
    }
}
