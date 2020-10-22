<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_coupons}}".
 *
 * @property int $id
 * @property int $detail_id
 * @property int $coupon_id
 * @property int $send_num 赠送数量
 * @property int $is_delete
 */
class VipCardCoupons extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_coupons}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['detail_id', 'coupon_id', 'send_num'], 'required'],
            [['detail_id', 'coupon_id', 'send_num', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'detail_id' => '子卡id',
            'coupon_id' => 'Coupon ID',
            'send_num' => '赠送数量',
            'is_delete' => 'Is Delete',
        ];
    }
}
