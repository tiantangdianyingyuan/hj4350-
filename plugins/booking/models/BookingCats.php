<?php

namespace app\plugins\booking\models;

use app\models\GoodsCats;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%booking_cats}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $cat_id
 * @property int $sort
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class BookingCats extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%booking_cats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'cat_id', 'sort', 'created_at', 'updated_at', 'deleted_at'], 'required'],
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
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getCats()
    {
        return $this->hasOne(GoodsCats::className(), ['id' => 'cat_id']);
    }
}
