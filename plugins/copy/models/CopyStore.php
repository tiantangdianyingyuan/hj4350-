<?php

namespace app\plugins\copy\models;

use Yii;

/**
 * This is the model class for table "{{%mall_members}}".
 *
 * @property int $id
 * @property int $store_id 门店id
 * @property int $url 门店链接
 * @property string $name 门店名称
 * @property int $ver 门店名称
 * @property string $store_name 小程序名称
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class CopyStore extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%copy_store}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['store_id', 'url', 'name', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['store_id','ver'], 'integer'],
            [['url','name','store_name'], 'string', 'max' => 150],
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
            'url' => '门店链接',
            'name' => '门店名称',
            'var' => '版本',
            'store_name' => '小程序名称',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }


}
