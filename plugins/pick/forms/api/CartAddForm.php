<?php


/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\models\GoodsAttr;
use app\models\Goods;
use app\models\Model;
use app\plugins\pick\models\PickCart;
use yii\helpers\ArrayHelper;

class CartAddForm extends Model
{
    public $goods_id;
    public $attr;
    public $num;
    public $pick_activity_id;

    public function rules()
    {
        return [
            [['goods_id', 'attr', 'num', 'pick_activity_id'], 'required'],
            [['goods_id', 'num', 'attr', 'pick_activity_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            PickCart::cacheStatusSet(true);
            $goods = Goods::findOne($this->goods_id);
            if (!$goods) {
                throw new \Exception('商品不存在');
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

            $cart = PickCart::findOne([
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->goods_id,
                'attr_id' => $this->attr,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if (!$cart) {
                $cart = new PickCart();
                $cart->mall_id = \Yii::$app->mall->id;
                $cart->user_id = \Yii::$app->user->id;
                $cart->goods_id = $this->goods_id;
                $cart->attr_id = $this->attr;
                $cart->num = 0;
                $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
                $cart->pick_activity_id = $this->pick_activity_id;
            };
            $cart->num += $this->num;
            if ($cart->save()) {
                PickCart::cacheStatusSet(false);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '添加购物车成功',
                ];
            } else {
                throw new \Exception($this->getErrorMsg($cart));
            }
        } catch (\Exception $e) {
            PickCart::cacheStatusSet(false);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
