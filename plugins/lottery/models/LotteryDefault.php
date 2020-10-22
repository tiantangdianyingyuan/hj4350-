<?php

namespace app\plugins\lottery\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%lottery_default}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $lottery_id
 * @property int $user_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class LotteryDefault extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lottery_default}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'lottery_id', 'user_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'lottery_id', 'user_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'lottery_id' => 'Lottery ID',
            'user_id' => 'User ID',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
