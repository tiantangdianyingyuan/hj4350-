<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/25
 * Time: 9:22
 */

namespace app\plugins\flash_sale\forms\api;

use app\core\response\ApiCode;
use app\events\CartEvent;
use app\models\Cart;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\flash_sale\forms\common\CommonSetting;
use app\plugins\flash_sale\models\FlashSaleActivity;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\FlashSaleGoodsAttr;
use app\plugins\flash_sale\models\Goods;
use app\plugins\flash_sale\Plugin;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

class IndexForm extends Model
{
    public $flash_goods_id;
    public $attr_id;
    public $num;

    public function rules()
    {
        return [
            [['flash_goods_id', 'num'], 'integer'],
            [['attr_id'], 'safe'],
        ];
    }

    public function addCart()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $activityIds = FlashSaleActivity::find()->andWhere(
                [
                    'status' => 1,
                    'mall_id' => Yii::$app->mall->id,
                    'is_delete' => 0
                ]
            )->select('id');
            /** @var FlashSaleGoods $flashSaleGoods */
            $flashSaleGoods = FlashSaleGoods::find()->where(
                [
                    'goods_id' => $this->flash_goods_id,
                    'is_delete' => 0,
                    'activity_id' => $activityIds,
                ]
            )->with('attr')->one();

            if (!$flashSaleGoods) {
                throw new Exception('限时抢购商品不存在');
            }

            $attr = null;
            foreach ($flashSaleGoods->goods->attr as $aItem) {
                if ($aItem->id == $this->attr_id) {
                    $attr = $aItem;
                    if ($aItem->stock < $this->num) {
                        throw new Exception('商品库存不足');
                    }
                }
            }
            if (!$attr) {
                throw new Exception('商品规格异常');
            }

            $cart = Cart::findOne(
                [
                    'user_id' => Yii::$app->user->id,
                    'goods_id' => $this->flash_goods_id,
                    'attr_id' => $this->attr_id,
                    'mall_id' => Yii::$app->mall->id,
                    'is_delete' => 0,
                ]
            );

            if (!$cart) {
                $cart = new Cart();
                $cart->mall_id = Yii::$app->mall->id;
                $cart->user_id = Yii::$app->user->id;
                $cart->goods_id = $this->flash_goods_id;
                $cart->attr_id = $this->attr_id;
                $cart->attr_info = Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
                $cart->sign = Yii::$app->plugin->getCurrentPlugin()->getName();
            }
            $cart->num += $this->num;
            $res = $cart->save();
            if (!$res) {
                throw new Exception($this->getErrorMsg($cart));
            }

            Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加购物车成功',
            ];
        } catch (Exception $e) {
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
        $query = Cart::find()->where(
            [
                'mall_id' => Yii::$app->mall->id,
                'is_delete' => 0,
                'user_id' => Yii::$app->user->id,
                'sign' => (new Plugin())->getName(),
            ]
        )
            ->with(['goods.goodsWarehouse'])
            ->with('attrs.memberPrice');
        $list = $query->all();

        $userIdentity = UserIdentity::findOne(['user_id' => Yii::$app->user->id]);

        $newList = [];
        /** @var Cart $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goods'] = $item->goods ? ArrayHelper::toArray($item->goods) : [];
            $newItem['goods']['goodsWarehouse'] = isset($item->goods->goodsWarehouse) ? ArrayHelper::toArray(
                $item->goods->goodsWarehouse
            ) : [];
            $newItem['attrs'] = $item->attrs ? ArrayHelper::toArray($item->attrs) : [];
            $newItem['attrs']['memberPrice'] = isset($item->attrs->memberPrice) ? ArrayHelper::toArray(
                $item->attrs->memberPrice
            ) : [];
            if ($item->attrs) {
                $newItem['attrs']['attr'] = (new Goods())->signToAttr($item->attrs->sign_id, $item->goods->attr_groups);
                $newItem['attr_str'] = 0;
            } else {
                $newItem['attr_str'] = 1;
            }
            $newItem['goods']['name'] = $item->goods ? $item->goods->getName() : '';
            $newItem['goods']['cover_pic'] = $item->goods ? $item->goods->getCoverPic() : '';

            try {
                /** @var FlashSaleGoods $flashSaleGoods */
                $flashSaleGoods = FlashSaleGoods::find()
                    ->where(['goods_id' => $item->goods->id])
                    ->joinWith(
                        [
                            'activity a' => function ($query) {
                                $query->where(
                                    [
                                        'a.mall_id' => Yii::$app->mall->id,
                                        'a.is_delete' => 0
                                    ]
                                );
                            }
                        ]
                    )
                    ->one();

                if (!$flashSaleGoods || $flashSaleGoods->activity->end_at < mysql_timestamp()) {
                    $flashSaleStatus = 0;
                    $flashSaleStatusLabel = '已结束';
                    $flashSaleTime = 0;
                } elseif ($flashSaleGoods->activity->status == 0 || ($flashSaleGoods->goods && $flashSaleGoods->goods->is_delete == 1)) {
                    $flashSaleStatus = 3;
                    $flashSaleStatusLabel = '已下架';
                    $flashSaleTime = 0;
                } elseif ($flashSaleGoods->activity->start_at > mysql_timestamp()) {
                    $flashSaleStatus = 2;
                    $flashSaleStatusLabel = '未开始';
                    $flashSaleTime = strtotime($flashSaleGoods->activity->start_at) - time();
                } elseif (
                    $flashSaleGoods->activity->start_at <= mysql_timestamp()
                    && $flashSaleGoods->activity->end_at >= mysql_timestamp()
                ) {
                    $flashSaleStatus = 1;
                    $flashSaleStatusLabel = '正在进行中';
                    $flashSaleTime = strtotime($flashSaleGoods->activity->end_at) - time();
                }
            } catch (Exception $exception) {
                Yii::error('购物车限时抢购商品：' . $exception->getMessage());
                $flashSaleStatus = 0;
                $flashSaleStatusLabel = '已结束';
                $flashSaleTime = 0;
            }
            $newItem['flash_sale_status'] = $flashSaleStatus;
            $newItem['flash_sale_status_label'] = $flashSaleStatusLabel;
            $newItem['flash_sale_time'] = $flashSaleTime;

            $setting = (new CommonSetting())->search();
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
                    $member = MallMembers::find()->where(
                        [
                            'status' => 1,
                            'is_delete' => 0,
                            'level' => $userIdentity->member_level,
                            'mall_id' => Yii::$app->mall->id
                        ]
                    )->one();
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
                try {
                    $value = FlashSaleGoodsAttr::findOne(['goods_attr_id' => $newItem['attr_id']]);
                    if ($newItem['flash_sale_status'] == 1) {
                        if ($value->type == 1) {
                            $discount = (1 - $value->discount / 10) * $newItem['attrs']['price'];
                            $newItem['attrs']['price'] -= min($discount, $newItem['attrs']['price']);
                            $newItem['attrs']['price'] = price_format($newItem['attrs']['price']);
                        } else {
                            $discount = $value->cut;
                            $newItem['attrs']['price'] -= min($discount, $newItem['attrs']['price']);
                            $newItem['attrs']['price'] = price_format($newItem['attrs']['price']);
                        }
                    }
                } catch (Exception $exception) {
                }

                if ($item->attr_info) {
                    try {
                        $attrInfo = Yii::$app->serializer->decode($item->attr_info);
                        $reducePrice = $attrInfo['price'] - $item->attrs->price;
                        if ($attrInfo['price'] - $item->attrs->price) {
                            $newItem['reduce_price'] = price_format($reducePrice);
                        }
                    } catch (Exception $exception) {
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
