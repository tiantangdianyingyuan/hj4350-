<?php

namespace app\plugins\ttapp\models;

use Yii;

/**
 * This is the model class for table "{{%ttapp_jump_appid}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $appid
 */
class TtappJumpAppid extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ttapp_jump_appid}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'appid'], 'required'],
            [['mall_id'], 'integer'],
            [['appid'], 'string', 'max' => 64],
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
            'appid' => 'Appid',
        ];
    }
}
