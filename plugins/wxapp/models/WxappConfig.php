<?php

namespace app\plugins\wxapp\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%wxapp_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $appid
 * @property string $appsecret
 * @property string $created_at
 * @property string $updated_at
 * @property string $mchid
 * @property string $key
 * @property string $cert_pem
 * @property string $key_pem
 * @property WxappService $service
 */
class WxappConfig extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'appid', 'appsecret', 'created_at', 'updated_at'], 'required'],
            [['mall_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['appid'], 'string', 'max' => 128],
            [['appsecret'], 'string', 'max' => 255],
            [['mchid', 'key'], 'string', 'max' => 32],
            [['cert_pem', 'key_pem'], 'string', 'max' => 2000],
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
            'appsecret' => 'Appsecret',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'mchid' => 'Mchid',
            'key' => 'Key',
            'cert_pem' => 'Cert Pem',
            'key_pem' => 'Key Pem',
        ];
    }

    public function getService()
    {
        return $this->hasOne(WxappService::className(), ['cid' => 'id']);
    }
}
