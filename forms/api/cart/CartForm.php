<?php

namespace app\forms\api\cart;

use app\core\response\ApiCode;
use app\models\Cart;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\MallMembers;
use app\models\Model;
use app\models\Store;
use app\models\UserIdentity;
use app\plugins\mch\models\Mch;
use app\plugins\Plugin;
use yii\helpers\ArrayHelper;

class CartForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['limit'], 'integer'],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        while (Cart::cacheStatusGet()) {
            // 购物车的编辑、删除等操作完成之后，才可以获取购物车列表
            usleep(500);
        }

        $list = Cart::find()->alias('c')->where([
            'c.mall_id' => \Yii::$app->mall->id,
            'c.is_delete' => 0,
            'c.user_id' => \Yii::$app->user->id,
            'c.sign' => '',
        ])
            ->with(['goods.goodsWarehouse', 'store'])
            ->with(['attrs.memberPrice' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])->orderBy(['c.created_at' => SORT_DESC])->all();

        $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
        $newList = [];
        // TODO 需要和秒杀代码整合
        /** @var Cart[] $list */
        foreach ($list as $item) {
            //隐藏商城面议商品
            if (empty($item->goods->sign)) {
                $mallGoods = $item->goods->mallGoods;
                if ($mallGoods && $mallGoods->is_negotiable == 1) {
                    continue;
                }
            }
            $newItem = ArrayHelper::toArray($item);
            $newItem['goods'] = ArrayHelper::toArray($item->goods);
            $newItem['store'] = ArrayHelper::toArray($item->store);
            $newItem['attrs'] = $item->attrs ? ArrayHelper::toArray($item->attrs) : $item->attrs;
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
            $newItem['goods']['name'] = $item->goods->name;
            $newItem['goods']['cover_pic'] = $item->goods->coverPic;

            // 购物车显示会员价
            if ($userIdentity && $userIdentity->member_level && $item->goods->is_level && $item->mch_id == 0 && $item->attrs) {
                if ($item->goods->is_level_alone) {
                    foreach ($item->attrs->memberPrice as $mItem) {
                        if ($mItem->level == $userIdentity->member_level) {
                            $newItem['attrs']['price'] = $mItem['price'] > 0 ? $mItem['price'] : $item->attrs->price;
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

            $newList[] = $newItem;
        }

        // 加入插件商品
        $list = $newList;
        $plugins = \Yii::$app->plugin->list;
        foreach ($plugins as $plugin) {
            $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getCartList')) {
                $list = array_merge($list, $object->getCartList());
            }
        }
        // 将数据按商城 商户区分
        $newDataList = [];
        foreach ($list as $item) {
            $newDataList[$item['mch_id']][] = $item;
        }

        $newList = [];
        foreach ($newDataList as $key => $item) {
            if ($key > 0) {
                $mch = Mch::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'status' => 1,
                    'review_status' => 1,
                    'id' => $key
                ]);
                $newItemList = [
                    'mch_id' => $key,
                    'is_active' => false,
                    'new_status' => 0,
                    'name' => isset($item[0]['store']) ? $item[0]['store']['name'] : '未知商户',
                    'goods_list' => [],
                ];

                foreach ($item as $gItem) {
                    $gItem['mch_status'] = !$mch ? 1 : 0;
                    $newGoods = $this->getNewStatus($gItem);
                    $newItemList['goods_list'][] = $newGoods;
                }
                $newList[] = $newItemList;
            } else {
                $newItemList = [
                    'mch_id' => 0,
                    'is_active' => false,
                    'new_status' => 0,
                    'name' => \Yii::$app->mall->name ?: '平台自营',
                    'goods_list' => []
                ];
                foreach ($item as $gItem) {
                    $newGoods = $this->getNewStatus($gItem);
                    $newItemList['goods_list'][] = $newGoods;
                }
                $newList[] = $newItemList;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ],
        ];
    }

    private function getNewStatus($item)
    {
        $item['is_active'] = false;
        $item['new_status'] = 0;// 正常
        // 秒杀已结束
        if ($item['sign'] == 'miaosha' && $item['miaosha_status'] != 1) {
            $item['new_status'] = 1;
        }
        // 商品已售罄
        if ($item['attrs'] && $item['attrs']['stock'] == 0) {
            $item['new_status'] = 2;
        }
        // 商品已失效
        if (!$item['attrs']) {
            $item['new_status'] = 3;
        }
        // 商户已关闭
        if (isset($item['mch_status']) && $item['mch_status']) {
            $item['new_status'] = 4;
        }
        // 商品已下架
        if ($item['goods']['status'] == 0) {
            $item['new_status'] = 5;
        }
        // 商品已删除
        if ($item['goods']['is_delete'] == 1) {
            $item['new_status'] = 3;
        }
        //限时抢购下架
        if ($item['sign'] == 'flash_sale' && $item['flash_sale_status'] == 3) {
            $item['new_status'] = 5;
        }
        //限时抢购未开始
        if ($item['sign'] == 'flash_sale' && $item['flash_sale_status'] == 2) {
            $item['new_status'] = 6;
        }
        //限时抢购已结束
        if ($item['sign'] == 'flash_sale' && $item['flash_sale_status'] == 0) {
            $item['new_status'] = 3;
        }
        return $item;
    }
}
