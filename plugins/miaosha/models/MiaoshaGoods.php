<?php

namespace app\plugins\miaosha\models;

use app\models\GoodsAttr;
use Yii;

/**
 * This is the model class for table "{{%miaosha_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $goods_warehouse_id
 * @property int $open_time 开放时间
 * @property string $open_date 开放日期
 * @property int $buy_limit 限单 -1|不限单
 * @property int $virtual_miaosha_num 虚拟秒杀量
 * @property int $is_delete
 * @property int $activity_id 活动ID
 * @property Goods $goods
 * @property GoodsAttr $attr
 * @property MiaoshaActivitys $activity
 */
class MiaoshaGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%miaosha_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'open_time', 'open_date', 'goods_warehouse_id'], 'required'],
            [['mall_id', 'goods_id', 'open_time', 'buy_limit', 'virtual_miaosha_num', 'is_delete',
                'goods_warehouse_id', 'activity_id'], 'integer'],
            [['open_date'], 'safe'],
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
            'goods_warehouse_id' => '商品库ID',
            'open_time' => '开放时间',
            'open_date' => '开放日期',
            'buy_limit' => '限单 -1|不限单',
            'virtual_miaosha_num' => '虚拟秒杀量',
            'is_delete' => 'Is Delete',
            'activity_id' => '活动ID',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(\app\models\Goods::className(), ['id' => 'goods_id']);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id'])->andWhere(['is_delete' => 0]);
    }

    public function getActivity()
    {
        return $this->hasOne(MiaoshaActivitys::className(), ['id' => 'activity_id'])->andWhere(['is_delete' => 0]);
    }
}
