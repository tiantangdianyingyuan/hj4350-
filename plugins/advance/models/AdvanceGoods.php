<?php

namespace app\plugins\advance\models;

use Yii;

/**
 * This is the model class for table "{{%advance_goods}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $mall_id
 * @property string $ladder_rules 阶梯规则
 * @property string $deposit
 * @property string $swell_deposit 定金膨胀金
 * @property string $start_prepayment_at 预售开始时间
 * @property string $end_prepayment_at 预售结束时间
 * @property int $pay_limit 尾款支付时间 -1:无限制
 * @property int $is_delete
 */
class AdvanceGoods extends \app\models\ModelActiveRecord
{
    // 插件商品编辑事件
    const EVENT_EDIT = 'advanceGoodsEdit';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'mall_id', 'start_prepayment_at', 'end_prepayment_at', 'pay_limit'], 'required'],
            [['goods_id', 'mall_id', 'pay_limit', 'is_delete'], 'integer'],
            [['deposit', 'swell_deposit'], 'number'],
            [['start_prepayment_at', 'end_prepayment_at'], 'safe'],
            [['ladder_rules'], 'string', 'max' => 4096],
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
            'mall_id' => 'Mall ID',
            'ladder_rules' => 'Ladder Rules',
            'deposit' => 'Deposit',
            'swell_deposit' => 'Swell Deposit',
            'start_prepayment_at' => 'Start Prepayment At',
            'end_prepayment_at' => 'End Prepayment At',
            'pay_limit' => 'Pay Limit',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
