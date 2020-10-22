<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_appoint_goods}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property string $created_at
 */
class VipCardAppointGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_appoint_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'created_at' => 'Created At',
        ];
    }
}
