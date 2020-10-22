<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%free_delivery_rules}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property int $type 1:订单满额包邮 2:订单满件包邮 3:单商品满额包邮 4:单商品满件包邮
 * @property string $price
 * @property string $detail
 * @property int $status 是否默认 0否 1是
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class FreeDeliveryRules extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%free_delivery_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'detail', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'type', 'status', 'mch_id'], 'integer'],
            [['price'], 'number'],
            [['detail', 'name'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'name' => '包邮规则名称',
            'type' => 'Type',
            'price' => '包邮金额',
            'status' => '是否默认',
            'detail' => 'Detail',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function decodeDetail()
    {
        $detail =  Yii::$app->serializer->decode($this->detail);
        if (!isset($detail[0]['condition'])) {
            $newItem['condition'] = $this->price;
            $newItem['list'] = $detail;
            $detail = [$newItem];
        }
        return $detail;
    }

    public function getTypeText()
    {
        switch ($this->type) {
            case 1:
                return '订单满额包邮';
            case 2:
                return '订单满件包邮';
            case 3:
                return '单商品满额包邮';
            case 4:
                return '单商品满件包邮';
            default:
                return '';
        }
    }
}
