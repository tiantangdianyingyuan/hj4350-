<?php

namespace app\plugins\pintuan\models;

use Yii;

/**
 * This is the model class for table "{{%pintuan_goods_share}}".
 *
 * @property int $id
 * @property string $share_commission_first 一级分销佣金比例
 * @property string $share_commission_second 二级分销佣金比例
 * @property string $share_commission_third 三级分销佣金比例
 * @property int $goods_id
 * @property int $goods_attr_id 商城商品规格ID
 * @property int $pintuan_goods_groups_id 拼团设置ID
 * @property int $pintuan_goods_attr_id 拼团商品规格ID
 * @property int $is_delete
 * @property int $level 分销商等级
 */
class PintuanGoodsShare extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_goods_share}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number'],
            [['goods_id', 'goods_attr_id', 'pintuan_goods_groups_id'], 'required'],
            [['goods_id', 'goods_attr_id', 'pintuan_goods_groups_id', 'pintuan_goods_attr_id', 'is_delete',
                'level'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'share_commission_first' => '一级分销佣金比例',
            'share_commission_second' => '二级分销佣金比例',
            'share_commission_third' => '三级分销佣金比例',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => '商城商品规格ID',
            'pintuan_goods_groups_id' => '拼团设置ID',
            'pintuan_goods_attr_id' => '拼团商品规格ID',
            'is_delete' => 'Is Delete',
            'level' => '分销商等级',
        ];
    }
}
