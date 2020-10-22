<?php

namespace app\plugins\mch\models;

use Yii;

/**
 * This is the model class for table "{{%mch_goods}}".
 *
 * @property int $id
 * @property int $mch_id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $sort
 * @property int $status 0.申请上架|1.申请中|2.同意上架|3.拒绝上架
 * @property string $remark 备注
 * @property int $is_delete
 */
class MchGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mch_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mch_id', 'mall_id', 'goods_id'], 'required'],
            [['mch_id', 'mall_id', 'goods_id', 'status', 'is_delete', 'sort'], 'integer'],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mch_id' => 'Mch ID',
            'mall_id' => 'Mall ID',
            'sort' => 'Sort',
            'goods_id' => 'Goods ID',
            'status' => '0.申请上架|1.申请中|2.同意上架|3.拒绝上架',
            'remark' => '备注',
            'is_delete' => 'Is Delete',
        ];
    }
}
