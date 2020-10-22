<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%form}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property int $status 是否启用
 * @property string $value 表单内容
 * @property int $is_default 是否默认
 * @property int $is_delete 是否删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Form extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%form}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'status', 'is_default', 'is_delete'], 'integer'],
            [['value', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['value'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'status' => '是否启用',
            'value' => '表单内容',
            'is_default' => '是否默认',
            'is_delete' => '是否删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
