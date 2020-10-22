<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_send_template_address}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 网点名称
 * @property string $username 联系人
 * @property string $mobile 联系电话
 * @property string $code 网点邮编
 * @property string $address 地址
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class OrderSendTemplateAddress extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_send_template_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'is_delete', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'username', 'mobile', 'code'], 'string', 'max' => 60],
            [['address'], 'string', 'max' => 255],
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
            'name' => '网点名称',
            'username' => '联系人',
            'mobile' => '联系电话',
            'code' => '网点邮编',
            'address' => '地址',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    /**
     * @param OrderSendTemplateAddress $templateAddress
     * @return array
     */
    public function getNewData($templateAddress)
    {
        $newAddress = json_decode($templateAddress->address);
        return [
            'id' => $templateAddress->id,
            'name' => $templateAddress->name,
            'username' => $templateAddress->username,
            'mobile' => $templateAddress->mobile,
            'code' => $templateAddress->code,
            'province' => $newAddress->province,
            'city' => $newAddress->city,
            'district' => $newAddress->district,
            'address' => $newAddress->address,
        ];
    }
}
