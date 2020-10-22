<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_notice}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $type update更新urgent紧急important重要
 * @property string $content
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class AdminNotice extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_notice}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'is_delete'], 'integer'],
            [['content'], 'required'],
            [['content', 'type'], 'string'],
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
            'user_id' => 'User ID',
            'type' => 'update更新urgent紧急important重要',
            'content' => 'Content',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
