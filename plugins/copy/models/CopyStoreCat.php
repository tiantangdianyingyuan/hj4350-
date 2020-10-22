<?php

namespace app\plugins\copy\models;

use Yii;

/**
 * This is the model class for table "{{%mall_members}}".
 *
 * @property int $id
 * @property int $store_id 门店id
 * @property int $cat_id 门店链接
 * @property int $name 门店链接
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class CopyStoreCat extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%copy_store_cat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['store_id', 'cat_id', 'name', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['store_id', 'cat_id'], 'integer'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => '门店id',
            'cat_id' => '分类id',
            'name' => '门店名称',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }


}
