<?php

namespace app\plugins\fxhb\models;

use Yii;

/**
 * This is the model class for table "{{%fxhb_activity_goods_relation}}".
 *
 * @property int $id
 * @property int $activity_id 活动ID
 * @property int $goods_warehouse_id 商品
 * @property int $is_delete 删除
 */
class FxhbActivityGoodsRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fxhb_activity_goods_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'goods_warehouse_id'], 'required'],
            [['activity_id', 'goods_warehouse_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动ID',
            'goods_warehouse_id' => '商品',
            'is_delete' => '删除',
        ];
    }
}
