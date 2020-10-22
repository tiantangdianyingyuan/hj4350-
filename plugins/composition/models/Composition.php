<?php

namespace app\plugins\composition\models;

use Yii;

/**
 * This is the model class for table "{{%composition}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 套餐名
 * @property string $price 套餐价
 * @property int $type 套餐类型 1--固定套餐 2--搭配套餐
 * @property int $status 是否上架 0--下架 1--上架
 * @property int $sort 排序
 * @property string $sort_price 排序的优惠金额
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property CompositionGoods[] $compositionGoods
 */
class Composition extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%composition}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'type', 'status', 'sort', 'is_delete'], 'integer'],
            [['price', 'sort_price'], 'number'],
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
            'id' => ' ',
            'mall_id' => 'Mall ID',
            'name' => '套餐名',
            'price' => '套餐价',
            'type' => '套餐类型 1--固定套餐 2--搭配套餐',
            'status' => '是否上架 0--下架 1--上架',
            'sort' => '排序',
            'sort_price' => '排序的优惠金额',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getCompositionGoods()
    {
        return $this->hasMany(CompositionGoods::className(), ['model_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getTypeText()
    {
        return $this->type == 1 ? '固定套餐' : '搭配套餐';
    }
}
