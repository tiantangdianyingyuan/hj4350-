<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cart}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id 商品
 * @property string $attr_id 商品规格
 * @property int $num 商品数量
 * @property int $mch_id 商户id
 * @property int $is_delete 删除
 * @property int $sign 删除
 * @property string $attr_info 规格信息
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 * @property Store $store
 * @property Goods $goods
 * @property GoodsAttr $attrs
 */
class Cart extends ModelActiveRecord
{
    /** @var string 购物车添加 */
    const EVENT_CART_ADD = 'cartAdd';

    /** @var string 购物车删除 */
    const EVENT_CART_DESTROY = 'cartDestroy';

    const CART_STATUS_CACHE = 'cart_status_cache';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'attr_id', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'num', 'mch_id', 'is_delete', 'attr_id'], 'integer'],
            [['created_at', 'deleted_at', 'updated_at', 'sign', 'attr_info'], 'safe'],
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
            'user_id' => 'User ID',
            'goods_id' => '商品',
            'attr_id' => '商品规格',
            'num' => '商品数量',
            'mch_id' => '商户id',
            'is_delete' => '删除',
            'sign' => '标记',
            'attr_info' => '规格信息',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function cacheStatusGet()
    {
        $cart_status_cache = self::CART_STATUS_CACHE . \Yii::$app->user->id;
        return \Yii::$app->cache->get($cart_status_cache);
    }

    public static function cacheStatusSet(bool $info)
    {
        $cart_status_cache = self::CART_STATUS_CACHE . \Yii::$app->user->id;
        \Yii::$app->cache->set($cart_status_cache, $info, 0);
    }

    public function getAttrs()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'attr_id'])->where(['is_delete' => 0]);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['mch_id' => 'mch_id']);
    }
}
