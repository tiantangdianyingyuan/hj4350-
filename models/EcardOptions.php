<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ecard_options}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $token 加密字符串
 * @property int $ecard_id 电子卡密id
 * @property string $value 卡密字段值
 * @property int $is_delete 是否删除
 * @property int $is_sales 是否出售
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_occupy 是否被占用 0--否 1--是
 * @property EcardData[] $data
 */
class EcardOptions extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ecard_options}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'value', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'ecard_id', 'is_delete', 'is_sales', 'is_occupy'], 'integer'],
            [['value'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
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
            'token' => '加密字符串',
            'ecard_id' => '电子卡密id',
            'value' => '卡密字段值',
            'is_delete' => '是否删除',
            'is_sales' => '是否出售',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_occupy' => '是否被占用 0--否 1--是',
        ];
    }

    public function getData()
    {
        return $this->hasMany(EcardData::className(), ['token' => 'token']);
    }
}
