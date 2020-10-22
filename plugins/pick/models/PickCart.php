<?php

namespace app\plugins\pick\models;

use app\models\Goods;
use app\models\GoodsAttr;
use Yii;

/**
 * This is the model class for table "{{%pick_cart}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id 商品
 * @property int $attr_id 商品规格
 * @property int $num 商品数量
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 * @property string $attr_info
 * @property int $pick_activity_id
 * @property $goods
 * @property $attrs
 */
class PickCart extends \app\models\ModelActiveRecord
{
    /** @var string 购物车添加 */
    const EVENT_CART_ADD = 'pickCartAdd';

    /** @var string 购物车删除 */
    const EVENT_CART_DESTROY = 'pickCartDestroy';

    const CART_STATUS_CACHE = 'pick_cart_status_cache';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pick_cart}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'attr_id', 'created_at', 'deleted_at', 'updated_at', 'pick_activity_id'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'attr_id', 'num', 'is_delete', 'pick_activity_id'], 'integer'],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['attr_info'], 'string'],
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
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'attr_info' => 'Attr Info',
            'pick_activity_id' => '活动id'
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
}
