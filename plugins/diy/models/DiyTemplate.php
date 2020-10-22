<?php

namespace app\plugins\diy\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%diy_template}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $data
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $type
 */
class DiyTemplate extends ModelActiveRecord
{
    public const TYPE_PAGE = 'page';
    public const TYPE_MODULE = '';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'data'], 'required'],
            [['mall_id', 'is_delete'], 'integer'],
            [['data'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 100],
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
            'name' => 'Name',
            'data' => 'Data',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'type' => 'page:模块',
        ];
    }
}
