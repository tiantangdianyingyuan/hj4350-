<?php

namespace app\plugins\pick\models;

use Yii;

/**
 * This is the model class for table "{{%pick_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 状态 0 关闭 1开启
 * @property int $goods_id
 * @property int $stock 总库存
 * @property int $sort
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $pick_activity_id 活动id
 * @property Goods $goods 活动商品
 * @property PickActivity $activity 活动
 */
class PickGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pick_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'goods_id', 'stock', 'sort', 'is_delete', 'pick_activity_id'], 'integer'],
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
            'status' => 'Status',
            'goods_id' => 'Goods ID',
            'stock' => 'Stock',
            'sort' => 'Sort',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'pick_activity_id' => 'Pick Activity ID',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getActivity()
    {
        return $this->hasOne(PickActivity::className(), ['id' => 'pick_activity_id'])
            ->andWhere(['is_delete' => 0]);
    }
}
