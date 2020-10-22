<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_card}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $card_id
 * @property string $name 名称
 * @property string $pic_url 图片
 * @property string $content 详情
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_use 是否使用 0--未使用 1--已使用
 * @property int $clerk_id 核销人id
 * @property int $store_id 门店ID
 * @property string $clerked_at  核销时间
 * @property int $order_id 发放卡券的订单id
 * @property int $order_detail_id 订单详情ID
 * @property string $data 额外信息字段
 * @property string $start_time
 * @property string $end_time
 * @property int $number
 * @property int $use_number
 * @property int $receive_id 转赠领取的用户id
 * @property int $parent_card_id 转赠的用户卡券id
 * @property GoodsCards $card
 * @property Order $order
 * @property OrderDetail $detail
 * @property Store $store
 * @property User $user
 * @property GoodsCardClerkLog $clerkLog
 * @property GoodsCardClerkLog $lastClerkLog
 * @property string $remark
 * @property User $receive 领取的用户
 * @property User $from 卡券来源的用户
 */
class UserCard extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_card}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'card_id', 'name', 'pic_url', 'content', 'created_at', 'updated_at', 'deleted_at', 'clerked_at'], 'required'],
            [['mall_id', 'user_id', 'card_id', 'is_delete', 'is_use', 'clerk_id', 'store_id', 'order_id', 'order_detail_id', 'use_number', 'number', 'receive_id', 'parent_card_id'], 'integer'],
            [['content', 'data'], 'string'],
            [['created_at', 'updated_at', 'deleted_at', 'clerked_at', 'start_time', 'end_time'], 'safe'],
            [['name', 'pic_url', 'remark'], 'string', 'max' => 255],
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
            'card_id' => 'Card ID',
            'name' => '名称',
            'pic_url' => '图片',
            'content' => '详情',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_use' => '是否使用 0--未使用 1--已使用',
            'clerk_id' => '核销人id',
            'store_id' => '门店ID',
            'clerked_at' => ' 核销时间',
            'order_id' => '发放卡券的订单id',
            'order_detail_id' => '订单详情ID',
            'data' => '额外信息字段',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'number' => '核销总次数',
            'use_number' => '已核销次数',
            'remark' => '备注',
            'receive_id' => '转赠领取的用户id',
            'parent_card_id' => '转赠的用户卡券id',
        ];
    }

    public function getClerk()
    {
        return $this->hasOne(User::className(), ['id' => 'clerk_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getCard()
    {
        return $this->hasOne(GoodsCards::className(), ['id' => 'card_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getDetail()
    {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }

    public function getClerkLog()
    {
        return $this->hasMany(GoodsCardClerkLog::className(), ['user_card_id' => 'id']);
    }

    public function getLastClerkLog()
    {
        return $this->hasOne(GoodsCardClerkLog::className(), ['user_card_id' => 'id'])->orderBy(['clerked_at' => SORT_DESC]);
    }

    /**
     * 兼容旧卡券
     * @param UserCard $userCard
     * @return array;
     */
    public function getNewItem($userCard)
    {
        $data = [];
        if ($userCard->is_use == 1 && $userCard->number - $userCard->use_number > 0) {
            $data['use_number'] = $userCard->number;
        }

        $data['is_show_history'] = 0;
        if ($userCard->is_use || $userCard->lastClerkLog) {
            $data['is_show_history'] = 1;
        }

        if ($userCard->lastClerkLog) {
            $data['store_name'] = $userCard->lastClerkLog->store->name;
            $data['clerked_at'] = new_date($userCard->lastClerkLog->clerked_at);
        }

        return $data;
    }

    public function getReceive()
    {
        return $this->hasOne(User::className(), ['id' => 'receive_id']);
    }

    public function getFrom()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])
            ->viaTable(UserCard::tableName(), ['id' => 'parent_card_id']);
    }
}
