<?php

namespace app\plugins\mch\models;

use Yii;

/**
 * This is the model class for table "{{%mch_visit_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property int $num
 * @property string $created_at
 * @property string $updated_at
 */
class MchVisitLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mch_visit_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'user_id', 'created_at'], 'required'],
            [['mall_id', 'mch_id', 'user_id', 'num'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'mch_id' => 'Mch ID',
            'user_id' => 'User ID',
            'num' => 'Num',
            'created_at' => 'Created At',
        ];
    }
}
