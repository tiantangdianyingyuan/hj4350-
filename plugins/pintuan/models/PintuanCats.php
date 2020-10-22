<?php

namespace app\plugins\pintuan\models;

use app\models\GoodsCats;
use Yii;

/**
 * This is the model class for table "{{%pintuan_cats}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $cat_id
 * @property int $sort
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class PintuanCats extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_cats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'cat_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'cat_id', 'sort', 'is_delete'], 'integer'],
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
            'cat_id' => 'Cat ID',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCats()
    {
        return $this->hasOne(GoodsCats::className(), ['id' => 'cat_id']);
    }
}
