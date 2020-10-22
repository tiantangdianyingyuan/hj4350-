<?php

namespace app\plugins\pintuan\models;

use app\models\GoodsAttr;
use Yii;

/**
 * This is the model class for table "{{%pintuan_goods_groups}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $people_num 拼团人数
 * @property int $group_num 团长数量
 * @property string $preferential_price 团长优惠
 * @property int $pintuan_time 拼团时间
 * @property int $is_delete
 * @property Goods $goods
 * @property PintuanGoodsAttr[] $attr
 * @property PintuanGoodsShare[] $shareLevel
 */
class PintuanGoodsGroups extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_goods_groups}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'people_num', 'pintuan_time', 'is_delete'], 'integer'],
            [['preferential_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'people_num' => '拼团人数',
            'group_num' => '团长数量',
            'preferential_price' => '团长优惠',
            'pintuan_time' => '拼团时间',
            'is_delete' => 'Is Delete',
        ];
    }

    // 插件优化后废弃
    public function getAttr()
    {
        return $this->hasMany(PintuanGoodsAttr::className(), [
            'pintuan_goods_groups_id' => 'id'
        ])->andWhere(['is_delete' => 0]);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getShare()
    {
        return $this->hasOne(PintuanGoodsShare::className(), ['pintuan_goods_groups_id' => 'id'])
            ->where(['is_delete' => 0, 'level' => 0]);
    }

    public function getShareLevel()
    {
        return $this->hasMany(PintuanGoodsShare::className(), ['pintuan_goods_groups_id' => 'id'])
            ->where(['is_delete' => 0]);
    }
}
