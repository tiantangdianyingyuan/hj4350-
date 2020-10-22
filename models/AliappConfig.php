<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%aliapp_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $appid
 * @property string $app_private_key
 * @property string $alipay_public_key
 * @property string $cs_tnt_inst_id
 * @property string $cs_scene
 * @property string $app_aes_secret
 * @property string $transfer_app_id
 * @property string $transfer_app_private_key
 * @property string $transfer_alipay_public_key
 * @property string $transfer_appcert
 * @property string $transfer_alipay_rootcert
 * @property string $created_at
 * @property string $updated_at
 */
class AliappConfig extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%aliapp_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'appid', 'app_private_key', 'alipay_public_key', 'created_at', 'updated_at'], 'required'],
            [['mall_id'], 'integer'],
            [['created_at', 'updated_at', 'transfer_alipay_public_key', 'transfer_appcert', 'transfer_alipay_rootcert'], 'safe'],
            [['appid', 'cs_tnt_inst_id', 'cs_scene', 'app_aes_secret', 'transfer_app_id'], 'string', 'max' => 32],
            [['app_private_key', 'alipay_public_key', 'transfer_app_private_key', ], 'string', 'max' => 2000],
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
            'app_private_key' => 'App Private Key',
            'alipay_public_key' => 'Alipay Public Key',
            'cs_tnt_inst_id' => 'Cs Tnt Inst ID',
            'cs_scene' => 'Cs Scene',
            'app_aes_secret' => '内容加密方式 AES密钥',
            'transfer_app_id' => '转账app_id',
            'transfer_app_private_key' => '转账应用私钥',
            'transfer_alipay_public_key' => '转账支付宝公钥',
            'transfer_appcert' => '转账应用公钥证书',
            'transfer_alipay_rootcert' => '转账支付宝根证书',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
