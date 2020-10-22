<?php

namespace app\plugins\community\models;

use app\models\Order;
use Yii;

/**
 * This is the model class for table "{{%community_activity}}".
 *
 * @property string $id
 * @property int $mall_id
 * @property int $status 状态 0下架 1上架
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $title 活动标题
 * @property string $start_at 活动开始时间
 * @property string $end_at 活动结束时间
 * @property int $is_area_limit 是否单独区域购买
 * @property string $area_limit
 * @property string $full_price 满减方案json
 * @property int $condition 0关闭，1开启人数条件，2开启件数条件
 * @property int $num 条件数量
 * @property CommunityGoods[] $communityGoods
 */
class CommunityActivity extends \app\models\ModelActiveRecord
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
        return '{{%community_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'area_limit'], 'required'],
            [['mall_id', 'status', 'is_delete', 'is_area_limit', 'condition', 'num'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'start_at', 'end_at'], 'safe'],
            [['area_limit'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['full_price'], 'string', 'max' => 200],
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
            'status' => '状态 0下架 1上架',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'title' => '活动标题',
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
            'is_area_limit' => '是否单独区域购买',
            'area_limit' => 'Area Limit',
            'full_price' => '满减方案json',
            'condition' => '0关闭，1开启人数条件，2开启件数条件',
            'num' => '条件数量',
        ];
    }

    public function getCommunityGoods()
    {
        return $this->hasMany(CommunityGoods::className(), ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getCommunityOrder()
    {
        return $this->hasMany(CommunityOrder::className(), ['activity_id' => 'id'])->alias('co')->leftJoin(['o' => Order::tableName()], 'o.id = order_id')
            ->andWhere(['o.is_pay' => 1, 'o.is_delete' => 0, 'o.is_recycle' => 0, 'co.is_delete' => 0]);//->andWhere(['!=', 'cancel_status', 1]);
    }

    public function getMiddlemanActivity()
    {
        return $this->hasOne(CommunityMiddlemanActivity::className(), ['activity_id' => 'id'])->andWhere(['middleman_id' => Yii::$app->user->id, 'is_delete' => 0]);
    }
}
