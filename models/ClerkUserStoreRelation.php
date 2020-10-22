<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%clerk_user_store_relation}}".
 *
 * @property int $id
 * @property int $clerk_user_id
 * @property int $store_id
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 */
class ClerkUserStoreRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%clerk_user_store_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clerk_user_id', 'store_id', 'created_at', 'deleted_at'], 'required'],
            [['clerk_user_id', 'store_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clerk_user_id' => 'Clerk User ID',
            'store_id' => 'Store ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
