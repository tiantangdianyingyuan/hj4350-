<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ecard_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $ecard_id
 * @property string $status 日志操作 add--添加 occupy--占用 sales--卖出 delete--删除
 * @property string $sign 插件标示
 * @property int $number 数量
 * @property string $created_at
 * @property int $goods_id 商品id
 */
class EcardLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ecard_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at'], 'required'],
            [['mall_id', 'ecard_id', 'number', 'goods_id'], 'integer'],
            [['created_at'], 'safe'],
            [['status', 'sign'], 'string', 'max' => 255],
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
            'ecard_id' => 'Ecard ID',
            'status' => '日志操作 add--添加 occupy--占用 sales--卖出 delete--删除',
            'sign' => '插件标示',
            'number' => '数量',
            'created_at' => 'Created At',
            'goods_id' => '商品id',
        ];
    }
}
