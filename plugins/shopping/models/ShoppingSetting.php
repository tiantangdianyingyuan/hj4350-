<?php

namespace app\plugins\shopping\models;

use Yii;

/**
 * This is the model class for table "{{%shopping_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $is_open 是否开启0.关闭|1.开启
 * @property string $created_at
 */
class ShoppingSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shopping_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at'], 'required'],
            [['mall_id', 'is_open'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'is_open' => '是否开启0.关闭|1.开启',
            'created_at' => 'Created At',
        ];
    }
}
