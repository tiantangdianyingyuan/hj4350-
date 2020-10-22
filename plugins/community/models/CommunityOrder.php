<?php

namespace app\plugins\community\models;

use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%community_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $activity_id 活动ID
 * @property int $user_id 用户ID
 * @property int $middleman_id 团长ID
 * @property string $profit_price 总利润
 * @property string $profit_data 利润详情
 * @property string $full_price 满多少
 * @property string $discount_price 优惠金额
 * @property int $num 商品件数
 * @property int $is_delete
 * @property string $activity_no 活动编号
 * @property int $no 编号
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property CommunityMiddleman $middleman
 * @property Order $order
 * @property CommunityActivity $activity
 * @property User $user
 */
class CommunityOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'activity_id', 'user_id', 'middleman_id', 'is_delete', 'no', 'num'], 'integer'],
            [['middleman_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['profit_price', 'full_price', 'discount_price'], 'number'],
            [['profit_data'], 'string'],
            [['activity_no'], 'string', 'max' => 100],
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
            'order_id' => 'Order ID',
            'activity_id' => '活动ID',
            'user_id' => '用户ID',
            'middleman_id' => '团长ID',
            'profit_price' => '总利润',
            'profit_data' => '利润详情',
            'full_price' => '满多少',
            'discount_price' => '优惠金额',
            'is_delete' => 'Is Delete',
            'activity_no' => '活动编号',
            'no' => '编号',
        ];
    }

    public function getMiddleman()
    {
        return $this->hasOne(CommunityMiddleman::className(), ['user_id' => 'middleman_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getActivity()
    {
        return $this->hasOne(CommunityActivity::className(), ['id' => 'activity_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBonusLog()
    {
        return $this->hasOne(CommunityBonusLog::className(), ['order_id' => 'order_id']);
    }

    public function getDetail()
    {
        return $this->hasMany(OrderDetail::className(), ['order_id' => 'order_id']);
    }
}
