<?php

namespace app\plugins\bdapp\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%bdapp_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $app_id
 * @property string $app_key
 * @property string $app_secret
 * @property string $pay_dealid
 * @property string $pay_private_key
 * @property string $pay_public_key
 * @property string $pay_app_key
 */
class BdappConfig extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bdapp_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['pay_private_key', 'pay_public_key'], 'string'],
            [['app_id'], 'string', 'max' => 16],
            [['pay_app_key', 'pay_dealid', 'app_key', 'app_secret'], 'string', 'max' => 64],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'pay_dealid' => 'Deal Id',
            'pay_private_key' => 'Pay Private Key',
            'pay_public_key' => '公钥',
            'pay_app_key' => 'Pay App Key',
        ];
    }
}
