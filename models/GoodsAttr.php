<?php

namespace app\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%goods_attr}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property string $sign_id 规格ID标识
 * @property int $stock 库存
 * @property string $price 价格
 * @property string $no 货号
 * @property int $weight 重量（克）
 * @property string $pic_url 规格图片
 * @property int $is_delete
 * @property GoodsMemberPrice[] $memberPrice
 * @property GoodsShare $share
 * @property GoodsShare[] $shareLevel
 */
class GoodsAttr extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'stock', 'weight', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['sign_id', 'no', 'pic_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'sign_id' => '规格ID标识',
            'stock' => '库存',
            'price' => '价格',
            'no' => '货号',
            'weight' => '重量（克）',
            'pic_url' => '规格图片',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getMemberPrice()
    {
        return $this->hasMany(GoodsMemberPrice::className(), ['goods_attr_id' => 'id'])->where(['is_delete' => 0]);
    }

    /**
     * @param $num integer 需要改变的数量
     * @param $type string 增加add|减少sub
     * @param $goodsAttrId integer|null 需要改变的规格的id
     * @param $goodsAttrSign string|null 需要改变的规格的sign_id
     * @return bool
     * @throws Exception
     */
    public function updateStock($num, $type, $goodsAttrId = null, $goodsAttrSign = null)
    {
        if ($goodsAttrId) {
            $goodsAttr = self::findOne(['id' => $goodsAttrId]);
            if (!$goodsAttr) {
                throw new Exception('错误的$goodsAttrId');
            }
        } elseif ($goodsAttrSign) {
            $goodsAttr = self::findOne(['sign_id' => $goodsAttrSign]);
            if (!$goodsAttr) {
                throw new Exception('错误的$goodsAttrSign');
            }
        } else {
            $goodsAttr = $this;
        }

        // 商品总库存也需要减掉
        /** @var Goods $goods */
        $goods = Goods::findOne($goodsAttr->goods_id);
        if (!$goods) {
            throw new \Exception('库存操作：商品ID(' . $goodsAttr->goods_id . ')不存在');
        }

        if ($type === 'add') {
            $goodsAttr->stock += $num;
            $goods->goods_stock += $num;
        } elseif ($type === 'sub') {
            if ($num > $goodsAttr->stock) {
                throw new Exception('库存不足');
            }
            $goodsAttr->stock -= $num;
            $goods->goods_stock -= $num;
        } else {
            throw new Exception('错误$type');
        }

        if (!$goodsAttr->save()) {
            throw new Exception((new Model())->getErrorMsg($goodsAttr));
        }

        if (!$goods->save()) {
            throw new \Exception((new Model())->getErrorMsg($goods));
        }

        return true;
    }

    public function getShare()
    {
        return $this->hasOne(GoodsShare::className(), ['goods_attr_id' => 'id'])
            ->where(['is_delete' => 0, 'level' => 0]);
    }

    public function getShareLevel()
    {
        return $this->hasMany(GoodsShare::className(), ['goods_attr_id' => 'id'])
            ->where(['is_delete' => 0]);
    }
}
