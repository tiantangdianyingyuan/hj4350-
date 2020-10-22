<?php

namespace app\plugins\diy\models;

use Yii;

/**
 * This is the model class for table "{{%core_template_type}}".
 *
 * @property int $id
 * @property int $template_id
 * @property string $type 模板适用地方
 * @property int $is_delete
 */
class CoreTemplateType extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_template_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'is_delete'], 'required'],
            [['template_id', 'is_delete'], 'integer'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template ID',
            'type' => '模板适用地方',
            'is_delete' => 'Is Delete',
        ];
    }
}
