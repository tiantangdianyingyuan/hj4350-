<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%core_validate_code_log}}".
 *
 * @property int $id
 * @property string $target
 * @property string $content
 * @property string $created_at
 */
class CoreValidateCodeLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_validate_code_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'required'],
            [['created_at'], 'safe'],
            [['target', 'content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'target' => 'Target',
            'content' => 'Content',
            'created_at' => 'Created At',
        ];
    }
}
