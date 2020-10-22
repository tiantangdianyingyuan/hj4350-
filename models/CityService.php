<?php

namespace app\models;

use Yii;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%city_service}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 配送名称
 * @property string $data
 * @property string $platform 所属平台
 * @property string $service_type 
 * @property int $distribution_corporation 配送公司 1.顺丰|2.闪送|3.美团配送|4.达达
 * @property string $shop_no 门店编号
 * @property string $created_at
 * @property int $is_delete
 */
class CityService extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'distribution_corporation', 'platform', 'service_type'], 'required'],
            [['id', 'mall_id', 'distribution_corporation', 'is_delete'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'shop_no', 'platform', 'service_type'], 'string', 'max' => 255],
            [['data'], 'string'],
            [['id'], 'unique'],
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
            'platform' => '所属平台',
            'name' => '配送名称',
            'distribution_corporation' => '配送公司 1.顺丰|2.闪送|3.美团配送|4.达达',
            'shop_no' => '门店编号',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
        ];
    }
}
