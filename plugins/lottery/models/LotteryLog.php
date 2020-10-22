<?php

namespace app\plugins\lottery\models;

use app\models\ModelActiveRecord;
use app\models\User;
use app\models\GoodsAttr;

/**
 * This is the model class for table "{{%lottery_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $lottery_id
 * @property int $status 0未抽奖 1待开奖 2未中奖 3中奖 4已领取
 * @property int $goods_id 规格id
 * @property int $child_id 上级id
 * @property string $lucky_code 幸运码
 * @property string $raffled_at 领取时间
 * @property string $obtain_at
 * @property string $created_at
 * @property string $token 订单表token
 */
class LotteryLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lottery_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'lottery_id', 'goods_id', 'lucky_code', 'created_at'], 'required'],
            [['mall_id', 'user_id', 'lottery_id', 'status', 'goods_id', 'child_id'], 'integer'],
            [['raffled_at', 'obtain_at', 'created_at'], 'safe'],
            [['lucky_code', 'token'], 'string', 'max' => 255],
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
            'lottery_id' => 'Lottery ID',
            'status' => '0待开奖 1未中奖 2中奖 3已领取 ',
            'goods_id' => '商品id',
            'child_id' => '上级id',
            'lucky_code' => '幸运码',
            'raffled_at' => '领取时间',
            'obtain_at' => 'Obtain At',
            'created_at' => 'Created At',
            'token' => '订单表token',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getChild()
    {
        return $this->hasOne(LotteryLog::className(), ['child_id' => 'user_id', 'lottery_id' => 'lottery_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
    public function getLottery()
    {
        return $this->hasOne(Lottery::className(), ['id' => 'lottery_id']);
    }

    public function getChildUser()
    {
        return $this->hasOne(User::className(), ['id' => 'child_id']);
    }
}
