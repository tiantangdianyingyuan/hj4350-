<?php

namespace app\models;

use app\plugins\mch\models\Mch;
use Yii;

/**
 * This is the model class for table "{{%clerk_user}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property Store $store
 */
class ClerkUser extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%clerk_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'mall_id', 'mch_id', 'is_delete'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMch()
    {
        return $this->hasOne(Mch::className(), ['id' => 'mch_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getStore()
    {
        return $this->hasMany(Store::className(), ['id' => 'store_id'])
            ->viaTable(ClerkUserStoreRelation::tableName(), ['clerk_user_id' => 'id', 'is_delete' => 'is_delete']);
    }
}
