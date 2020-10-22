<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%we7_app}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $acid 微擎应用的acid
 * @property int $is_delete
 */
class We7App extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%we7_app}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'acid'], 'required'],
            [['mall_id', 'acid', 'is_delete'], 'integer'],
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
            'acid' => '微擎应用的acid',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMall()
    {
        return $this->hasOne(Mall::class, ['id' => 'mall_id']);
    }
}
