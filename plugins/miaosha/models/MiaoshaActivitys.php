<?php

namespace app\plugins\miaosha\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%miaosha_activitys}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 秒杀活动状态0.关闭|1.开启
 * @property string $open_date 活动开始时间
 * @property string $end_date 活动结束时间
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property MiaoshaGoods $miaoshaGoods
 * @property MiaoshaGoods $oneMiaoshaGoods
 */
class MiaoshaActivitys extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%miaosha_activitys}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'is_delete'], 'integer'],
            [['open_date', 'end_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'status' => '秒杀活动状态0.关闭|1.开启',
            'open_date' => '活动开始时间',
            'end_date' => '活动结束时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMiaoshaGoods()
    {
        return $this->hasMany(MiaoshaGoods::class, ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getOneMiaoshaGoods()
    {
        return $this->hasOne(MiaoshaGoods::class, ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    /**
     * @param MiaoshaActivitys $activity
     * @return string
     */
    public function getActivityStatus($activity)
    {
        $status = '未知';
        try {
            $todayDate = date('Y-m-d');
            if ($activity->open_date > $todayDate) {
                $status = '未开始';
            } elseif ($activity->open_date <= $todayDate && $activity->end_date >= $todayDate) {
                $status = '进行中';
            } elseif ($activity->end_date < $todayDate) {
                $status = '已结束';
            }

            if ($activity->status == 0) {
                $status = '下架中';
            }
        }catch (\Exception $exception) {
        }

        return $status;
    }
}
