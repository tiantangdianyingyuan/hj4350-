<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%city_deliveryman}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 配送员名称
 * @property string $mobile 联系方式
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 */
class CityDeliveryman extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city_deliveryman}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'is_delete'], 'integer'],
            [['name', 'mobile', 'is_delete', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'mobile'], 'string', 'max' => 255],
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
            'name' => '配送员名称',
            'mobile' => '联系方式',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
