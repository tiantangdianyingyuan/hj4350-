<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $activity_id 活动id
 * @property int $sort 排序
 * @property Goods $goods 商品
 * @property CommunityActivity $activity 活动
 */
class CommunityGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'goods_id', 'is_delete', 'activity_id', 'sort'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'activity_id' => '活动id',
            'sort' => '排序',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getActivity()
    {
        return $this->hasOne(CommunityActivity::className(), ['id' => 'activity_id'])
            ->andWhere(['is_delete' => 0]);
    }
}
