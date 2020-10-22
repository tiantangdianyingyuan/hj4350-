<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_cart}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property int $activity_id
 * @property int $community_goods_id
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property string $attr_info
 * @property int $num
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property CommunityGoods $communityGoods
 */
class CommunityCart extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_cart}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'user_id', 'activity_id', 'community_goods_id', 'goods_id', 'goods_attr_id', 'num', 'is_delete'], 'integer'],
            [['attr_info', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['attr_info'], 'string'],
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
            'mch_id' => 'Mch ID',
            'user_id' => 'User ID',
            'activity_id' => 'Activity ID',
            'community_goods_id' => 'Community Goods ID',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'attr_info' => 'Attr Info',
            'num' => 'Num',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getCommunityGoods()
    {
        return $this->hasOne(CommunityGoods::className(), ['id' => 'community_goods_id']);
    }
}
