<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_cards}}".
 *
 * @property int $id
 * @property int $detail_id 子卡id
 * @property int $card_id 卡券id
 * @property int $send_num 赠送数量
 * @property int $is_delete
 */
class VipCardCards extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_cards}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['detail_id', 'card_id', 'send_num'], 'required'],
            [['detail_id', 'card_id', 'send_num', 'is_delete'], 'integer'],
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
            'card_id' => '卡券id',
            'send_num' => '赠送数量',
            'is_delete' => 'Is Delete',
        ];
    }
}
