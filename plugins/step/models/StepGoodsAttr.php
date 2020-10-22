<?php
namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_goods_attr}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $attr_id 规格
 * @property string $currency 活力币
 * @property string $goods_id 活力币
 */
class StepGoodsAttr extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'attr_id', 'goods_id'], 'required'],
            [['mall_id', 'attr_id', 'goods_id'], 'integer'],
            [['currency'], 'number'],
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
            'attr_id' => '规格',
            'currency' => '活力币',
            'goods_id' => '商品ID',
        ];
    }
}
