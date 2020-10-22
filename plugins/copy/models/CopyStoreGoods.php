<?php

namespace app\plugins\copy\models;

use Yii;

/**
 * This is the model class for table "{{%mall_members}}".
 *
 * @property int $id
 * @property int $store_id 门店id
 * @property int $goods_id 商品id
 * @property int $cat_id 分类id
 * @property string $name 商品名称
 * @property string $pic_url 图片
 * @property array $goods_info goods_info
 * @property int $is_copy 是否已经导入
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class CopyStoreGoods extends \app\models\ModelActiveRecord
{



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%copy_store_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['store_id', 'cat_id', 'name', 'goods_id','created_at', 'updated_at', 'deleted_at'], 'required'],
            [['store_id', 'cat_id','is_copy','goods_id'], 'integer'],
            [['name','pic_url'], 'string', 'max' => 200],
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
            'goods_id' => '商品id',
            'cat_id' => '分类id',
            'name' => '商品名称',
            'pic_url' => '图片',
            'is_copy' => '已导入',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }


}
