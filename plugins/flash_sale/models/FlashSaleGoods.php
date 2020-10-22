<?php

namespace app\plugins\flash_sale\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%flash_sale_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 状态 0 关闭 1开启
 * @property int $goods_id
 * @property int $type 优惠方式 1打折  2减钱  3促销价
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $activity_id 活动id
 * @property int $sort 排序
 * @property Goods $goods
 * @property FlashSaleActivity $activity
 */
class FlashSaleGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%flash_sale_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'goods_id', 'type', 'is_delete', 'activity_id', 'sort'], 'integer'],
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
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'activity_id' => 'Activity ID',
            'sort' => 'Sort',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getActivity()
    {
        return $this->hasOne(FlashSaleActivity::className(), ['id' => 'activity_id'])
            ->andWhere(['is_delete' => 0]);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }
}
