<?php

namespace app\plugins\pick\models;

use Yii;

/**
 * This is the model class for table "{{%pick_activity}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 状态 0 关闭 1开启
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $title 活动标题
 * @property string $start_at 活动开始时间
 * @property string $end_at 活动结束时间
 * @property string $rule_price 组合方案 元
 * @property int $rule_num 组合方案 件
 * @property int $is_area_limit 是否单独区域购买
 * @property string $area_limit
 * @property PickGoods $goods
 */
class PickActivity extends \app\models\ModelActiveRecord
{
    //上架状态
    public const ACTIVITY_UP = 1;
    //下架状态
    public const ACTIVITY_DOWN = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pick_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'rule_price', 'rule_num'], 'required'],
            [['mall_id', 'status', 'is_delete', 'rule_num', 'is_area_limit'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'start_at', 'end_at'], 'safe'],
            [['rule_price'], 'number'],
            [['area_limit'], 'string'],
            [['title'], 'string', 'max' => 255],
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
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'title' => '活动名称',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'rule_price' => '组合方案(元)',
            'rule_num' => '组合方案(件)',
            'is_area_limit' => 'Is Area Limit',
            'area_limit' => 'Area Limit',
        ];
    }

    public function getPickGoods()
    {
        return $this->hasMany(PickGoods::className(), ['pick_activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
