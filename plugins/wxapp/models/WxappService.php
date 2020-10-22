<?php

namespace app\plugins\wxapp\models;

use Yii;

/**
 * This is the model class for table "{{%wxapp_service}}".
 *
 * @property int $id
 * @property int $cid wxapp_config
 * @property string $appid 服务商appid
 * @property string $mchid 服务商mchid
 * @property int $is_choise 1选中  0不选
 * @property string $created_at
 * @property string $updated_at
 * @property string $key 服务商微信支付Api密钥
 * @property string $cert_pem
 * @property string $key_pem
 */
class WxappService extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cid', 'appid', 'mchid', 'created_at', 'updated_at', 'key'], 'required'],
            [['cid', 'is_choise'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['appid'], 'string', 'max' => 128],
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
            'cid' => 'Cid',
            'appid' => 'Appid',
            'mchid' => 'Mchid',
            'is_choise' => 'Is Choise',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'key' => 'Key',
            'cert_pem' => 'Cert Pem',
            'key_pem' => 'Key Pem',
        ];
    }
}
