<?php

namespace app\plugins\flash_sale\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%flash_sale_activity}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 状态 0下架 1上架
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $title 活动标题
 * @property string $start_at 活动开始时间
 * @property string $end_at 活动结束时间
 * @property int $notice 活动预告 0不进行预告 1立即预告 2开始前N小时进行预告
 * @property int $notice_hours 提前N小时
 */
class FlashSaleActivity extends ModelActiveRecord
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
        return '{{%flash_sale_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'is_delete', 'notice', 'notice_hours'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'start_at', 'end_at'], 'safe'],
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
            'title' => 'Title',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'notice' => 'Notice',
            'notice_hours' => 'Notice Hours',
        ];
    }

    public function getFlashSaleGoods()
    {
        return $this->hasMany(FlashSaleGoods::className(), ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
