<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_bonus_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $order_id
 * @property int $activity_id 活动ID
 * @property string $desc
 * @property string $price
 * @property string $profit_price 利润
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class CommunityBonusLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_bonus_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'order_id', 'activity_id', 'is_delete'], 'integer'],
            [['price', 'profit_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['desc'], 'string', 'max' => 200],
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
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'activity_id' => '活动ID',
            'desc' => 'Desc',
            'price' => 'Price',
            'profit_price' => '利润',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
