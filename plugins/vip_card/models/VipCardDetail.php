<?php

namespace app\plugins\vip_card\models;

use app\models\Coupon;
use app\models\GoodsCards;
use Yii;

/**
 * This is the model class for table "{{%vip_card_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $vip_id
 * @property string $name 标题
 * @property string $cover 子卡封面
 * @property int $expire_day
 * @property string $price
 * @property int $num 库存
 * @property int $sort 排序
 * @property int $send_integral_num 积分赠送
 * @property int $send_integral_type 积分赠送类型 1.固定值|2.百分比
 * @property string $send_balance 赠送余额
 * @property string $title 使用说明
 * @property string $content 使用内容
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class VipCardDetail extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vip_id', 'mall_id', 'name', 'expire_day', 'price', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['vip_id', 'expire_day', 'num', 'sort', 'send_integral_num', 'send_integral_type', 'is_delete', 'status'], 'integer'],
            [['price', 'send_balance'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'title'], 'string', 'max' => 255],
            [['content', 'cover'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vip_id' => 'Vip ID',
            'name' => 'Name',
            'cover' => '子卡封面',
            'expire_day' => 'Expire Day',
            'price' => 'Price',
            'num' => 'Num',
            'sort' => 'Sort',
            'send_integral_num' => 'Send Integral Num',
            'send_integral_type' => 'Send Integral Type',
            'send_balance' => 'Send Balance',
            'title' => 'Title',
            'content' => 'Content',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMain()
    {
        return $this->hasOne(VipCard::className(),['id' => 'vip_id']);
    }

    public function getVipCards()
    {
        return $this->hasMany(GoodsCards::className(), ['id' => 'card_id'])
            ->via('cards')->andWhere(['is_delete' => 0]);
    }

    public function getVipCoupons()
    {
        return $this->hasMany(Coupon::className(), ['id' => 'coupon_id'])
            ->via('coupons')->andWhere(['is_delete' => 0]);
    }

    public function getCards()
    {
        return $this->hasMany(VipCardCards::className(), ['detail_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getCoupons()
    {
        return $this->hasMany(VipCardCoupons::className(), ['detail_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
