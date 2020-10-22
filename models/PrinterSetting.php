<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%printer_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $printer_id 打印机id
 * @property int $block_id 模板id
 * @property int $is_attr 0不使用规格 1使用规格
 * @property string $type 打印方式
 * @property string $big 倍数
 * @property string $status 是否启用
 * @property int $store_id 门店ID
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Printer $printer
 * @property Store $store
 * @property int $show_type
 * @property string $order_send_type
 */
class PrinterSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'printer_id', 'type', 'created_at', 'updated_at', 'deleted_at', 'show_type'], 'required'],
            [['mall_id', 'printer_id', 'block_id', 'is_attr', 'is_delete', 'status', 'mch_id', 'store_id', 'big'], 'integer'],
            [['type', 'show_type', 'order_send_type'], 'string'],
            [['order_send_type'], 'string', 'max' => 255],
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
            'printer_id' => '打印机id',
            'block_id' => '模板id',
            'is_attr' => '0不使用规格 1使用规格',
            'big' => '倍数',
            'type' => '打印方式',
            'show_type' => '显示方式',
            'status' => '是否启用',
            'is_delete' => '删除',
            'order_send_type' => '订单发货方式',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getPrinter()
    {
        return $this->hasOne(Printer::className(), ['id' => 'printer_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }
}
