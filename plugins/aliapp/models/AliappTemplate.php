<?php

namespace app\plugins\aliapp\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%aliapp_template}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $tpl_name
 * @property string $tpl_id
 * @property string $created_at
 * @property string $updated_at
 */
class AliappTemplate extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%aliapp_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'tpl_name'], 'required'],
            [['mall_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['tpl_name', 'tpl_id'], 'string', 'max' => 255],
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
