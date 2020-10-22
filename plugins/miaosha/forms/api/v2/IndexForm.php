<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api\v2;

use app\core\response\ApiCode;
use app\events\CartEvent;
use app\models\Cart;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;
use yii\helpers\ArrayHelper;
use app\plugins\miaosha\forms\common\v2\SettingForm;

class IndexForm extends Model
{
    public $miaosha_goods_id;
    public $attr_id;
    public $num;

    public function rules()
    {
        return [
            [['miaosha_goods_id', 'num'], 'integer'],
            [['attr_id'], 'safe'],
        ];
    }

    public function addCart()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $activityIds = MiaoshaActivitys::find()->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
            /** @var MiaoshaGoods $miaoshaGoods */
            $miaoshaGoods = MiaoshaGoods::find()->where([
                'goods_id' => $this->miaosha_goods_id,
                'is_delete' => 0,
                'open_date' => date('Y-m-d'),
                'open_time' => date('H'),
                'activity_id' => $activityIds,
            ])->with('attr')->one();

            if (!$miaoshaGoods) {
                throw new \Exception('秒杀商品不存在');
            }

            $attr = null;
            foreach ($miaoshaGoods->attr as $aItem) {
                if ($aItem->id == $this->attr_id) {
                    $attr = $aItem;
                    if ($aItem->stock < $this->num) {
                        throw new \Exception('商品库存不足');
                    }
                }
            }
            if (!$attr) {
                throw new \Exception('商品规格异常');
            }

            $cart = Cart::findOne([
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->miaosha_goods_id,
                'attr_id' => $this->attr_id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if (!$cart) {
                $cart = new Cart();
                $cart->mall_id = \Yii::$app->mall->id;
                $cart->user_id = \Yii::$app->user->id;
                $cart->goods_id = $this->miaosha_goods_id;
                $cart->attr_id = $this->attr_id;
                $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
                $cart->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
            };
            $cart->num += $this->num;
            $res = $cart->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($cart));
            }

            \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加购物车成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getCartList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = Cart::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
            'sign' => (new Plugin())->getName(),
        ])
            ->with(['goods.goodsWarehouse'])
            ->with('attrs.memberPrice');
        $list = $query->all();

        $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);

        $newList = [];
        /** @var Cart $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goods'] = $item->goods ? ArrayHelper::toArray($item->goods) : [];
            $newItem['goods']['goodsWarehouse'] = isset($item->goods->goodsWarehouse) ? ArrayHelper::toArray($item->goods->goodsWarehouse) : [];
            $newItem['attrs'] = $item->attrs ? ArrayHelper::toArray($item->attrs) : [];
            $newItem['attrs']['memberPrice'] = isset($item->attrs->memberPrice) ? ArrayHelper::toArray($item->attrs->memberPrice) : [];
            if ($item->attrs) {
                $newItem['attrs']['attr'] = (new Goods())->signToAttr($item->attrs->sign_id, $item->goods->attr_groups);
                $newItem['attr_str'] = 0;
            } else {
                $newItem['attr_str'] = 1;
            }
            $newItem['goods']['name'] = $item->goods ? $item->goods->getName() : '';
            $newItem['goods']['cover_pic'] = $item->goods ? $item->goods->getCoverPic() : '';

            try {
                /** @var MiaoshaGoods $miaoshaGoods */
                $miaoshaGoods = MiaoshaGoods::find()->where(['goods_id' => $item->goods->id])->one();
                $H = strlen($miaoshaGoods->open_time) > 1 ? $miaoshaGoods->open_time : '0' . $miaoshaGoods->open_time;
                $startTime = strtotime($miaoshaGoods->open_date . ' ' . $H . ':00:00');

                $activity = MiaoshaActivitys::find()->where([
                    'status' => 1,
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $miaoshaGoods->activity_id
                ])->one();

                if (strtotime($miaoshaGoods->open_date) < strtotime(date('Y-m-d')) || !$activity) {
                    $miaoshaStatus = 0;
                    $miaoshaStatusLabel = '已结束';
                    $miaoshaTime = 0;
                } elseif ($miaoshaGoods->open_date == date('Y-m-d')) {
                    if ($miaoshaGoods->open_time > date('H')) {
                        $miaoshaStatus = 2;
                        $miaoshaStatusLabel = '未开始';
                        $miaoshaTime = $startTime - time();
                    } elseif ($miaoshaGoods->open_time == date('H')) {
                        $miaoshaStatus = 1;
                        $miaoshaStatusLabel = '正在进行中';
                        $time = strtotime(date('Y-m-d H') . ':00:00') + 60 * 60;
                        $miaoshaTime = $time - time();
                    } else {
                        $miaoshaStatus = 0;
                        $miaoshaStatusLabel = '已结束';
                        $miaoshaTime = 0;
                    }
                } else {
                    $miaoshaStatus = 2;
                    $miaoshaStatusLabel = '未开始';
                    $miaoshaTime = $startTime - time();
                }
            }catch (\Exception $exception) {
                \Yii::error('购物车秒杀商品：' . $exception->getMessage());
                $miaoshaStatus = 0;
                $miaoshaStatusLabel = '已结束';
                $miaoshaTime = 0;
            }

            $newItem['miaosha_status'] = $miaoshaStatus;
            $newItem['miaosha_status_label'] = $miaoshaStatusLabel;
            $newItem['miaosha_time'] = $miaoshaTime;

            $setting = (new SettingForm())->search();
            if ($setting['is_member_price'] && $userIdentity && $userIdentity->member_level && $item->goods->is_level) {
                if ($item->goods->is_level_alone) {
                    /** @var GoodsMemberPrice $mItem */
                    foreach ($item->attrs->memberPrice as $mItem) {
                        if ($mItem->level == $userIdentity->member_level) {
                            $newItem['attrs']['price'] = $mItem->price > 0 ? $mItem->price : $item->attrs->price;
                            break;
                        }
                    }
                } else {
                    /** @var MallMembers $member */
                    $member = MallMembers::find()->where([
                        'status' => 1,
                        'is_delete' => 0,
                        'level' => $userIdentity->member_level,
                        'mall_id' => \Yii::$app->mall->id
                    ])->one();
                    if ($member) {
                        $newItem['attrs']['price'] = round(($member->discount / 10) * $item->attrs->price, 2);
                    }
                }
            }

            $newItem['reduce_price'] = 0;
            if ($item->attrs) {
                // 还存在的商品
                $newItem['attrs']['attr'] = (new Goods())->signToAttr($item->attrs->sign_id, $item->goods->attr_groups);
                $newItem['attr_str'] = 0;
                if ($item->attr_info) {
                    try {
                        $attrInfo = \Yii::$app->serializer->decode($item->attr_info);
                        $reducePrice = $attrInfo['price'] - $item->attrs->price;
                        if ($attrInfo['price'] - $item->attrs->price) {
                            $newItem['reduce_price'] = price_format($reducePrice);
                        }
                    } catch (\Exception $exception) {
                    }
                }
            } else {
                $newItem['attr_str'] = 1;
            }
            $newList[] = $newItem;
        }

        return $newList;
    }
}
