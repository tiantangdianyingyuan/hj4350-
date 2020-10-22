<?php

namespace app\models;

/**
 * This is the model class for table "{{%goods_attr_template}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $attr_group_name 规格名
 * @property int $attr_group_id 规格组
 * @property string $attr_list 规格值
 * @property string $select_attr_list 后台 搜索用的
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class GoodsAttrTemplate extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_attr_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'attr_list', 'select_attr_list'], 'required'],
            [['mall_id', 'mch_id', 'attr_group_id', 'is_delete'], 'integer'],
            [['attr_list', 'select_attr_list'], 'string'],
            [['created_at', 'deleted_at'], 'safe'],
            [['attr_group_name'], 'string', 'max' => 255],
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
            'attr_group_name' => '规格名',
            'attr_group_id' => '规格组',
            'attr_list' => '规格值',
            'select_attr_list' => '后台 搜索用的',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
