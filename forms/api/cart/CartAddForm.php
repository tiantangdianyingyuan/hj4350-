<?php

namespace app\forms\api\cart;

use app\core\response\ApiCode;
use app\events\CartEvent;
use app\models\Cart;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use yii\helpers\ArrayHelper;

class CartAddForm extends Model
{
    public $goods_id;
    public $attr;
    public $num;
    public $mch_id;

    public function rules()
    {
        return [
            [['goods_id', 'attr', 'num'], 'required'],
            [['goods_id', 'num', 'attr', 'mch_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            Cart::cacheStatusSet(true);
            $goods = Goods::findOne($this->goods_id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->goodsWarehouse->type == 'ecard') {
                throw new \Exception('虚拟商品不允许加入购物车');
            }

            $attr = GoodsAttr::find()->alias('g')->where([
                'g.id' => $this->attr,
                'g.goods_id' => $this->goods_id,
                'g.is_delete' => 0,
            ])->innerJoinwith(['goods o' => function ($query) {
                $query->where([
                    'o.id' => $this->goods_id,
                    'o.mall_id' => \Yii::$app->mall->id,
                    'o.is_delete' => 0,
                    'o.status' => 1,
                ]);
            }])->one();

            if (!$attr) {
                throw new \Exception('商品异常');
            }

            $this->num = $this->num > $attr->stock ? $attr->stock : $this->num;
            if ($this->num <= 0) {
                throw new \Exception('数量为空或库存为空');
            }

            $cart = Cart::findOne([
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->goods_id,
                'attr_id' => $this->attr,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if (!$cart) {
                $cart = new Cart();
                $cart->mall_id = \Yii::$app->mall->id;
                $cart->user_id = \Yii::$app->user->id;
                $cart->goods_id = $this->goods_id;
                $cart->attr_id = $this->attr;
                $cart->num = 0;
                $cart->mch_id = $goods->mch_id;
                $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
            };
            $cart->num += $this->num;
            if ($cart->num > $attr->stock) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '商品加购件数超过库存'
                ];
            }

            if ($cart->save()) {
                \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '添加购物车成功',
                ];
            } else {
                throw new \Exception($this->getErrorMsg($cart));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function __destruct()
    {
        Cart::cacheStatusSet(false);
    }
}
