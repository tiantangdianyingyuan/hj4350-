<?php

namespace app\models;

/**
 * This is the model class for table "{{%goods_hot_search}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id 商品id
 * @property string $title 热搜词
 * @property int $sort 排序
 * @property string $type 类型
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 */
class GoodsHotSearch extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public const TYPE_GOODS = 'goods';
    public const TYPE_HOT_SEARCH = 'hot-search';

    public static function tableName()
    {
        return '{{%goods_hot_search}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'title', 'type', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'goods_id', 'sort', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 100],
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
            'goods_id' => '商品id',
            'title' => '热搜词',
            'sort' => '排序',
            'type' => '类型',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
