<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_cats}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $parent_id 父级ID
 * @property string $name 分类名称
 * @property string $pic_url
 * @property string $sort 排序，升序
 * @property string $big_pic_url
 * @property string $advert_pic 广告图片
 * @property string $advert_url 广告链接
 * @property int $status 是否启用:0.禁用|1.启用
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $is_show
 * @property string $advert_open_type 打开方式
 * @property string $advert_params 导航参数
 * @property GoodsCats[] $child
 * @property GoodsCats $parent
 * @property Goods $goods
 * @property GoodsCatRelation[] $goodsCatRelation
 */
class GoodsCats extends ModelActiveRecord
{
    // 分类编辑事件
    const EVENT_EDIT = 'goodsCatsEdit';

    // 分类删除事件
    const EVENT_DESTROY = 'goodsCatsDestroy';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_cats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'parent_id', 'status', 'is_delete', 'sort', 'mch_id', 'is_show', 'is_show'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['advert_params'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['pic_url', 'big_pic_url', 'advert_pic', 'advert_url',], 'string', 'max' => 255],
            [['advert_open_type'], 'string', 'max' => 65],
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
            'mch_id' => 'Mch ID',
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'pic_url' => 'Pic Url',
            'sort' => 'Sort',
            'is_show' => 'Is Show',
            'big_pic_url' => 'Big Pic Url',
            'advert_pic' => 'Advert Pic',
            'advert_url' => 'Advert Url',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'advert_open_type' => '打开方式',
            'advert_params' => '导航参数',
        ];
    }

    public function getMallGoods()
    {
        return $this->hasMany(MallGoods::className(), ['id' => 'goods_warehouse_id'])
            ->viaTable(GoodsCatRelation::tableName(), ['cat_id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getGoods()
    {
        return $this->hasMany(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->viaTable(GoodsCatRelation::tableName(), ['cat_id' => 'id'])->where(['is_delete' => 0]);
    }

    public function getParent()
    {
        return $this->hasOne(GoodsCats::className(), ['id' => 'parent_id']);
    }

    public function getChild()
    {
        return $this->hasMany(GoodsCats::className(), ['parent_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getGoodsCatRelation()
    {
        return $this->hasMany(GoodsCatRelation::className(), ['cat_id' => 'id'])
            ->where(['is_delete' => 0]);
    }
}
