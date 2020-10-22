<?php

namespace app\plugins\ttapp\models;

use Yii;

/**
 * This is the model class for table "{{%ttapp_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $mch_id 商户号
 * @property string $app_key
 * @property string $app_secret
 * @property string $pay_app_secret
 * @property string $pay_app_id
 * @property string $alipay_app_id
 * @property string $alipay_public_key
 * @property string $alipay_private_key
 * @property string $created_at
 * @property string $updated_at
 */
class TtappConfig extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ttapp_config}}';
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
            [['alipay_public_key', 'alipay_private_key'], 'string'],
            [['mch_id', 'pay_app_id', 'app_key', 'app_secret'], 'string', 'max' => 64],
            [['pay_app_secret', 'alipay_app_id'], 'string', 'max' => 128],
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
            'mch_id' => '商户号',
            'pay_app_secret' => 'Pay App Secret',
            'pay_app_id' => 'Pay App ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
