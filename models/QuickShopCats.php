<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%quick_shop_cats}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $cat_id
 * @property int $sort 排序
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class QuickShopCats extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%quick_shop_cats}}';
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
            'sort' => '排序',
            'is_delete' => '删除',
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
