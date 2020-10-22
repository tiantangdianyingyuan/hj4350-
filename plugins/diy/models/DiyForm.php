<?php

namespace app\plugins\diy\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%diy_form}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $form_data
 * @property string $created_at
 * @property int $is_delete
 * @property string $updated_at
 * @property string $deleted_at
 * @property User $user
 */
class DiyForm extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_form}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'form_data', 'created_at', 'is_delete', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'is_delete'], 'integer'],
            [['form_data'], 'string'],
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
            'mall_id' => 'Mall ID',
            'user_id' => 'User ID',
            'form_data' => 'Form Data',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
