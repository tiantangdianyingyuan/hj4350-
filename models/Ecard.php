<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ecard}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 卡密名称
 * @property string $content 使用说明
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $list 卡密字段
 * @property int $sales 已售
 * @property int $stock 库存
 * @property int $is_unique 是否去重 0--否 1--是
 * @property int $pre_stock 占用的库存
 * @property int $total_stock 总库存
 * @property EcardOptions[] $ecardOptions
 * @property GoodsWarehouse[] $goodsWarehouse
 */
class Ecard extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ecard}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'list'], 'required'],
            [['mall_id', 'is_delete', 'sales', 'stock', 'is_unique', 'pre_stock', 'total_stock'], 'integer'],
            [['content', 'list'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => '卡密名称',
            'content' => '使用说明',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'list' => '卡密字段',
            'sales' => '已售',
            'stock' => '库存',
            'is_unique' => '是否去重 0--否 1--是',
            'pre_stock' => '占用的库存',
            'total_stock' => '总库存',
        ];
    }

    public function getEcardOptions()
    {
        return $this->hasMany(EcardOptions::className(), ['ecard_id' => 'id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasMany(GoodsWarehouse::className(), ['ecard_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
