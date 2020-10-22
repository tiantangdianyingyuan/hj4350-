<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_switch}}".
 *
 * @property int $id
 * @property int $middleman_id
 * @property int $activity_id
 * @property int $goods_id
 * @property int $is_delete
 */
class CommunitySwitch extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_switch}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['middleman_id', 'activity_id', 'goods_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'middleman_id' => 'Middleman ID',
            'activity_id' => 'Activity ID',
            'goods_id' => 'Goods ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public static function getSwitch($goods_id, $middleman_id)
    {
        return self::findOne(['middleman_id' => $middleman_id, 'goods_id' => $goods_id, 'is_delete' => 0]);
    }
}
