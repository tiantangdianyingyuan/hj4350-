<?php

namespace app\plugins\lottery\models;

use app\models\ModelActiveRecord;


/**
 * This is the model class for table "{{%lottery}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id 规格
 * @property int $status 0关闭 1开启
 * @property int $type 0未完成 1已完成
 * @property int $stock 库存
 * @property string $start_at
 * @property string $end_at
 * @property int $sort 排序
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $join_min_num
 * @property int $participant 参与人
 * @property int $invitee 被邀请人
 * @property int $code_num 抽奖券码数量
 * @property LotteryLog[] $log
 * @property Goods $buy_goods_id
 * @property
 */
class Lottery extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lottery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'start_at', 'end_at', 'created_at', 'updated_at', 'deleted_at', 'buy_goods_id'], 'required'],
            [['mall_id', 'goods_id', 'status', 'type', 'stock', 'join_min_num', 'sort', 'is_delete', 'participant', 'invitee', 'code_num', 'buy_goods_id'], 'integer'],
            [['start_at', 'end_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'goods_id' => '规格',
            'status' => '0关闭 1开启',
            'type' => '0未完成 1已完成',
            'stock' => '库存',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'sort' => '排序',
            'is_delete' => '删除',
            'join_min_num' => "最小参与人数",
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'participant' => '参与人',
            'invitee' => '被邀请人',
            'code_num' => '抽奖券码数量',
            'buy_goods_id' => '购买商品ID'
        ];
    }

    public function getGoods()
    {
        return $this->hasone(Goods::className(), ['id' => 'goods_id']);
    }

    public function getLog()
    {
        return $this->hasMany(LotteryLog::className(), ['lottery_id' => 'id']);
    }
}
