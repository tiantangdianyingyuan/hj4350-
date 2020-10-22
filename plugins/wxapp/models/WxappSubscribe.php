<?php

namespace app\plugins\wxapp\models;

use Yii;

/**
 * This is the model class for table "{{%wxapp_subscribe}}".
 *
 * @property string $id
 * @property int $mall_id
 * @property string $tpl_name
 * @property string $tpl_id
 * @property string $created_at
 * @property string $updated_at
 */
class WxappSubscribe extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_subscribe}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at'], 'required'],
            [['mall_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['tpl_name'], 'string', 'max' => 65],
            [['tpl_id'], 'string', 'max' => 255],
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
            'tpl_name' => 'Tpl Name',
            'tpl_id' => 'Tpl ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
