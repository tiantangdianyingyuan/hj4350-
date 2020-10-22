<?php

namespace app\plugins\advance\models;

use app\forms\api\order\OrderException;
use app\models\Goods;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%advance_goods_attr}}".
 *
 * @property int $id
 * @property string $deposit 商品所需定金
 * @property string $swell_deposit 定金膨胀金
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $is_delete
 * @property int $advance_num
 */
class AdvanceGoodsAttr extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deposit', 'swell_deposit'], 'number'],
            [['goods_id', 'goods_attr_id'], 'required'],
            [['goods_id', 'goods_attr_id', 'is_delete', 'advance_num'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'deposit' => 'Deposit',
            'swell_deposit' => 'Swell Deposit',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
            'advance_num' => '预约数量',
        ];
    }
}
