<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-21
 * Time: 09:21
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\api;


use app\forms\api\order\OrderException;
use app\models\Order;
use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\forms\common\CommonSetting;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionOrder;
use app\plugins\composition\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        $plugin = new Plugin();
        $this->setSign($plugin->getName())
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableIntegral(false)
            ->setEnableMemberPrice(false)
            ->setEnableCoupon($setting['is_coupon'])
            ->setEnableAddressEnable($setting['is_territorial_limitation'])
            ->setEnableFullReduce($setting['is_full_reduce']);
        return $this;
    }

    public function getMchListData($formMchList)
    {
        $listData = [];
        $formDataList = [];
        foreach ($formMchList as $i => $formMchItem) {
            foreach ($formMchItem['goods_list'] as $goods) {
                if (!isset($goods['form_data']) || $goods['form_data'] == null) {
                    continue;
                }
                $formDataList[$goods['id']] = $goods['form_data'];
            }
            foreach ($formMchItem['composition_list'] as &$composition) {
                foreach ($composition['goods_list'] as &$goodsItem) {
                    if (isset($formDataList[$goodsItem['id']])) {
                        $goodsItem['form_data'] = $formDataList[$goodsItem['id']];
                    }
                }
                unset($goodsItem);
            }
            unset($composition);
            try {
                $compositionList = $this->getCompositionList($formMchItem['composition_list']);
            } catch (\Exception $exception) {
                throw new OrderException($exception->getMessage());
            }
            $goodsList = [];
            foreach ($compositionList as $composition) {
                $goodsList = array_merge($goodsList, $composition['goods_list']);
            }
            $formMchItem['goods_list'] = [];
            foreach ($formMchItem['composition_list'] as $composition) {
                $formMchItem['goods_list'] = array_merge($formMchItem['goods_list'], $composition['goods_list']);
            }

            $compositionAllDiscount = 0;
            foreach ($compositionList as &$item) {
                $compositionAllDiscount += $item['composition_price'];
            }
            $mchItem = [
                'mch' => $this->getMchInfo($formMchItem['mch_id']),
                'goods_list' => $goodsList,
                'composition_list' => $compositionList,
                'form_data' => $formMchItem,
                'insert_rows' => [
                    [
                        'title' => '套餐组合优惠',
                        'value' => '-¥' . price_format($compositionAllDiscount),
                        'data' => price_format(0 - $compositionAllDiscount),
                    ],
                ],
            ];
            $listData[] = $mchItem;
        }
        return $listData;
    }

    public function getCompositionList($compositionList)
    {
        $list = [];
        foreach ($compositionList as $compositionItem) {
            /* @var Composition $composition */
            $composition = Composition::find()->with(['compositionGoods'])
                ->where([
                    'id' => $compositionItem['composition_id'], 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id
                ])->one();
            if (!$composition) {
                throw new \Exception('套餐已删除');
            }
            if ($composition->status != 1) {
                throw new \Exception('套餐已下架');
            }
            self::$composition = $composition;
            self::$compositionList[$composition->id] = $composition;
            $model = $this->getModel($composition->id);
            $model->checkComposition($compositionItem['goods_list']); // 检验套餐是否可以被购买
            $compositionPrice = $model->getCompositionPrice($compositionItem['goods_list']); // 套餐优惠
            $goodsList = $this->getGoodsListData($compositionItem['goods_list']); // 获取套餐商品的详细信息
            $originGoodsList = $goodsList; // 备份原商品列表
            uasort($goodsList, function ($a, $b) {
                if ($a['total_price'] == $b['total_price']) {
                    return 0;
                }
                return ($a['total_price'] < $b['total_price']) ? -1 : 1;
            });
            $goodsList = array_values($goodsList);
            $price = array_sum(array_column($goodsList, 'total_price')); // 固定套餐--商品规格总价  搭配套餐--套餐总价
            $totalPrice = $price - $compositionPrice; // 套餐价
            $model->attrTotalPrice = $totalPrice;
            $resetPrice = $totalPrice;
            foreach ($goodsList as $index => &$goods) {
                $goodsAttr = $goods['goods_attr'];
                if ($composition->type == 1) {
                    $goodsPrice = price_format($goodsAttr['price'] * $totalPrice / $price); // 商品优惠后的价格
                    if ($resetPrice < $goodsPrice || ($index == count($goodsList) - 1 && $resetPrice > 0)) {
                        $goodsPrice = $resetPrice;
                    }
                    $resetPrice -= $goodsPrice;
                    $goods['composition_price'] = price_format($goodsAttr['price'] - $goodsPrice); // 商品优惠金额
                    $goods['goods_attr']['composition_price'] = price_format($goodsAttr['price'] - $goodsPrice); // 商品优惠金额
                } else {
                    $goodsCompositionPrice = $model->getGoodsPrice($goodsAttr['goods_id'], $goodsAttr['id'], $goodsAttr['price']); // 商品优惠金额
                    $goods['composition_price'] = price_format($goodsCompositionPrice);
                    $goods['goods_attr']['composition_price'] = price_format($goodsCompositionPrice);
                    $goodsPrice = price_format($goodsAttr['price'] - $goodsCompositionPrice);
                }
                $goods['total_price'] = price_format($goodsPrice); // 商品优惠后的价格
            }
            unset($goods);
            // 根据旧商品列表顺序或得新的商品列表start--->
            $tempGoodsList = [];
            foreach ($originGoodsList as $originGoods) {
                foreach ($goodsList as $newGoods) {
                    if (($originGoods['goods_attr'])->id == ($newGoods['goods_attr'])->id) {
                        $tempGoodsList[] = $newGoods;
                    }
                }
            }
            $list[] = [
                'composition_id' => $compositionItem['composition_id'],
                'name' => $composition->name,
                'type' => $composition->type,
                'original_price' => $price,
                'total_price' => price_format($totalPrice),
                'composition_price' => price_format($compositionPrice),
                'goods_list' => $tempGoodsList,
            ];
        }
        return $list;
    }

    public static $compositionList;
    public static $composition;

    protected function getGoodsItemData($item)
    {
        $list = parent::getGoodsItemData($item);
        $list['composition_id'] = self::$composition->id;
        return $list;
    }

    public function getGoodsAttrClass()
    {
        $class = new OrderGoodsAttr();
        $class->composition = self::$composition;
        return $class;
    }

    protected function getModel($compositionId)
    {
        $composition = self::$compositionList[$compositionId];
        $model = FactoryCombination::getCommon()->getCombinationList($composition->id, $composition->type);
        $model->composition = $composition;
        return $model;
    }

    public function checkGoodsOrderLimit($goodsList)
    {
        // 套餐不支持商品限单
        return true;
    }

    protected function checkGoodsBuyLimit($goodsList)
    {
        // 套餐不支持商品数量限购
        return true;
    }

    public function setVipDiscountData($mchItem)
    {
        // 套餐不支持超级会员卡
        return $mchItem;
    }

    protected function getSendType($mchItem)
    {
        $setting = CommonSetting::getCommon()->getSetting();
        return $setting['send_type'];
    }

    public function extraOrder($order, $mchItem)
    {
        foreach ($mchItem['composition_list'] as $composition) {
            $compositionOrder = new CompositionOrder();
            $compositionOrder->mall_id = $order->mall_id;
            $compositionOrder->order_id = $order->id;
            $compositionOrder->composition_id = $composition['composition_id'];
            $compositionOrder->price = $composition['composition_price'];
            $compositionOrder->is_delete = 0;
            if (!$compositionOrder->save()) {
                throw new OrderException($this->getErrorMsg($compositionOrder));
            }
        }
    }
}
