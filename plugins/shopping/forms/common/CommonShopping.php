<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\forms\common;


use app\models\Cart;
use app\models\Goods;
use app\models\Mall;
use app\models\MallSetting;
use app\models\Model;
use app\models\Order;
use app\models\User;
use app\plugins\aliapp\Plugin;
use app\plugins\shopping\models\ShoppingBuys;
use app\plugins\shopping\models\ShoppingLikes;
use app\plugins\shopping\models\ShoppingLikeUsers;
use app\plugins\shopping\models\ShoppingSetting;
use Curl\Curl;

class CommonShopping extends Model
{
    /** @var  Order $order */
    private $order;
    /** @var  User $user */
    private $user;
    /** @var  Goods $goods */
    private $goods;

    /**
     * 已购好物圈
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function buyList($orderId)
    {
        try {
            $this->findOrder($orderId);
            $this->checkIsOpen($this->order->mall_id);
            $this->getUser();
            \Yii::$app->setMall(Mall::findOne($this->order->mall_id));
            $productInfo = $this->getGoodInfo();
            $address = $this->getAddress();
            $district = $this->getDistrict();
            $contactTel = MallSetting::findOne(['mall_id' => $this->order->mall_id, 'key' => 'contact_tel']);

            $postData = [
                "order_list" => [
                    [
                        "order_id" => $this->order->order_no,
                        "create_time" => strtotime($this->order->created_at),
                        "pay_finish_time" => strtotime($this->order->pay_time),
                        "desc" => $this->order->remark,
                        "fee" => $this->order->total_pay_price * 100,
                        "trans_id" => $this->order->order_no,
                        "status" => $this->getOrderStatus(),
                        "ext_info" => [
                            "product_info" => [
                                "item_list" => $productInfo
                            ],
                            "express_info" => [
                                "name" => $this->order->name,
                                "phone" => $this->order->mobile,
                                "address" => $this->order->address ?: '无需物流(支持到店自提)',
                                "price" => $this->order->express_price * 100,
                                "national_code" => "",
                                "country" => '',
                                "province" => isset($address[0]) ? $address[0] : '',
                                "city" => isset($address[1]) ? $address[1] : '',
                                "district" => $district,
                            ],
                            // 商家信息
                            "brand_info" => [
                                "phone" => $contactTel->value,
                                "contact_detail_page" => [
                                    "path" => "/pages/index/index"
                                ]
                            ],
                            "payment_method" => $this->order->pay_type == 1 ? 1 : 2,
                            "user_open_id" => $this->user->userInfo->platform_user_id,
                            "order_detail_page" => [
                                "path" => 'pages/order/order-detail/order-detail?id=' . $this->order->id,
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/importorder?action=add-order&access_token=" .
                \Yii::$app->wechat->getAccessToken(),
                $postData
            );

            $shoppingBuys = new ShoppingBuys();
            $shoppingBuys->mall_id = $this->order->mall_id;
            $shoppingBuys->user_id = $this->order->user_id;
            $shoppingBuys->order_id = $this->order->id;
            $shoppingBuys->created_at = mysql_timestamp();
            $res = $shoppingBuys->save();
            if (!$res) {
                throw new \Exception('微信好物圈同步到商城失败:' . $this->getErrorMsg($shoppingBuys));
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 更新已购清单商品
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function updateBuyGood($orderId)
    {
        try {
            $this->findOrder($orderId);
            $this->checkIsOpen($this->order->mall_id);
            $this->getUser();
            \Yii::$app->setMall(Mall::findOne($this->order->mall_id));
            $address = $this->getAddress();
            $district = $this->getDistrict();

            if ($this->order->send_type == 1) {
                throw new \Exception('到店自提订单没有物流信息,无法更新');
            }

            if ($this->order->send_type == 2) {
                throw new \Exception('同城配送订单没有物流信息,无法更新');
            }

            $goodList = [];
            foreach ($this->order->detail as $item) {
                $goodList[] = [
                    'item_code' => $this->order->sign . $item->goods_id,
                    'sku_id' => $item->id
                ];
            }

            $postData = [
                "order_list" => [
                    [
                        "order_id" => $this->order->order_no,
                        "trans_id" => $this->order->order_no,
                        "status" => $this->getOrderStatus(),
                        'desc' => '',
                        "ext_info" => [
                            "express_info" => [
                                "name" => $this->order->name,
                                "phone" => $this->order->mobile,
                                "address" => $this->order->address,
                                "price" => $this->order->express_price * 100,
                                "national_code" => "",
                                "country" => "",
                                "province" => isset($address[0]) ? $address[0] : '',
                                "city" => isset($address[1]) ? $address[1] : '',
                                "district" => $district,
                                "express_package_info_list" => [
                                    [
                                        "express_company_id" => '',// 快递公司ID
                                        "express_company_name" => $this->order->express,
                                        "express_code" => $this->order->express_no,
                                        "ship_time" => strtotime($this->order->send_time),
                                        "express_page" => [
                                            "path" => "/pages/order/express-detail/express-detail?id="
                                                . $this->order->id
                                                . '&express=' . $this->order->express
                                                . '&express_no=' . $this->order->express_no
                                        ],
                                        "express_goods_info_list" => $goodList
                                    ]
                                ]
                            ],
                            "user_open_id" => $this->user->userInfo->platform_user_id,
                            "order_detail_page" => [
                                "path" => '/pages/order/order-detail/order-detail?id=' . $this->order->id
                            ]
                        ]
                    ]
                ]
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/importorder?action=update-order&access_token=" .
                \Yii::$app->wechat->getAccessToken(),
                $postData
            );
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 删除已买好物圈商品
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function destroyBuyGood($orderId)
    {
        try {
            $this->findOrder($orderId);
            $this->checkIsOpen($this->order->mall_id);
            $this->getUser();
            \Yii::$app->setMall(Mall::findOne($this->order->mall_id));
            $postData = [
                "user_open_id" => $this->user->userInfo->platform_user_id,
                "order_id" => $this->order->order_no
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/deleteorder?access_token=" . \Yii::$app->wechat->getAccessToken(),
                $postData
            );

            /** @var ShoppingBuys $shopping */
            $shopping = ShoppingBuys::find()->where(['order_id' => $this->order->id])->one();
            $shopping->is_delete = 1;
            $res = $shopping->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($shopping));
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 购物车 想买清单
     * @param $cartId
     * @return bool
     * @throws \Exception
     */
    public function addLikeList($cartId)
    {
        try {
            if (!$cartId) {
                throw new \Exception('cartId为空');
            }

            $cart = Cart::findOne($cartId);
            if (!$cart) {
                throw new \Exception('购物车:商品不存在');
            }

            $this->checkIsOpen($cart->mall_id);
            $this->getUser($cart->user_id);
            \Yii::$app->setMall(Mall::findOne($cart->mall_id));
            $this->getGoods($cart->goods_id);
            $cats = $this->getGoodsCats();
            $attrList = $this->getGoodsAttrList();
            $selectAttr = $this->getSelectGoodsAttr($cart->attr_id);
            $picList = $this->getGoodsPicList();

            $postData = [
                "user_open_id" => $this->user->userInfo->platform_user_id,
                "sku_product_list" => [
                    [
                        "item_code" => $this->goods->sign . $this->goods->id,
                        "title" => $this->goods->goodsWarehouse->name,
                        "desc" => "",
                        "category_list" => $cats ?: ['未知分类'],
                        "image_list" => $picList,
                        "src_wxapp_path" => "/pages/goods/goods?id=" . $this->goods->id,
                        "attr_list" => $attrList,
                        "update_time" => strtotime($cart['created_at']),
                        "sku_info" => [
                            "sku_id" => $this->goods->id,
                            "price" => $this->goods->price * 100,
                            "original_price" => $this->goods->goodsWarehouse->original_price * 100,
                            "status" => $this->goods->status ? 1 : 2,
                            "sku_attr_list" => $selectAttr,
                        ]
                    ]
                ]
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/addshoppinglist?access_token=" .
                \Yii::$app->wechat->getAccessToken(),
                $postData
            );

            // 同步数据到商城
            $shoppingLikes = ShoppingLikes::find()->where([
                'goods_id' => $this->goods->id,
                'mall_id' => $cart['mall_id'],
                'is_delete' => 0,
            ])->one();
            if (!$shoppingLikes) {
                $shoppingLikes = new ShoppingLikes();
                $shoppingLikes->mall_id = $cart['mall_id'];
                $shoppingLikes->goods_id = $this->goods->id;
                $res = $shoppingLikes->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($shoppingLikes));
                }
            }

            $shoppingLikeUsers = ShoppingLikeUsers::find()->where([
                'user_id' => $cart['user_id'], 'like_id' => $shoppingLikes->id
            ])->one();
            if (!$shoppingLikeUsers) {
                $shoppingLikeUsers = new ShoppingLikeUsers();
                $shoppingLikeUsers->user_id = $cart['user_id'];
                $shoppingLikeUsers->like_id = $shoppingLikes->id;
                $res = $shoppingLikeUsers->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($shoppingLikeUsers));
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 删除购物车想买清单商品
     * @param $cartIds
     * @return bool
     * @throws \Exception
     */
    public function destroyLikeList($cartIds)
    {
        try {
            if (!$cartIds) {
                throw new \Exception('cartIds不存在');
            }

            foreach ($cartIds as $id) {
                $cart = Cart::find()->where(['id' => $id])->one();
                $this->checkIsOpen($cart['mall_id']);
                \Yii::$app->setMall(Mall::findOne($cart['mall_id']));
                $this->getUser($cart['user_id']);
                $this->getGoods($cart['goods_id']);

                $postData = [
                    "user_open_id" => $this->user->userInfo->platform_user_id,
                    "sku_product_list" => [
                        [
                            "item_code" => $this->goods->sign . $cart['goods_id'],
                            "sku_id" => $this->goods->sign . $cart['goods_id'],
                        ]
                    ]
                ];

                $this->sendRequest(
                    "https://api.weixin.qq.com/mall/deleteshoppinglist?access_token=" .
                    \Yii::$app->wechat->getAccessToken(),
                    $postData
                );

                /** @var ShoppingLikes $shoppingLikes */
                $shoppingLikes = ShoppingLikes::find()->where([
                    'goods_id' => $cart['goods_id'],
                    'is_delete' => 0,
                    'mall_id' => $cart['mall_id']
                ])->one();
                if (!$shoppingLikes) {
                    throw new \Exception('无关联用户记录');
                }

                /** @var ShoppingLikeUsers $shoppingLikeUsers */
                $shoppingLikeUsers = ShoppingLikeUsers::find()->where([
                    'like_id' => $shoppingLikes->id, 'user_id' => $cart['user_id']
                ])->one();
                if ($shoppingLikeUsers) {
                    $shoppingLikeUsers->is_delete = 1;
                    $res = $shoppingLikeUsers->save();
                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($shoppingLikeUsers));
                    }
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 添加 想买用户
     * @param $userId
     * @param $likeId
     * @return bool
     * @throws \Exception
     */
    public function addLikeUser($userId, $likeId)
    {
        try {
            if (!$userId) {
                throw new \Exception('userId为空');
            }

            if (!$likeId) {
                throw new \Exception('likeId为空');
            }

            $this->getUser($userId);
            /** @var ShoppingLikes $shoppingLikes */
            $shoppingLikes = ShoppingLikes::find()->where([
                'id' => $likeId,
                'mall_id' => $this->user->mall_id,
                'is_delete' => 0,
            ])->one();
            if (!$shoppingLikes) {
                throw new \Exception('想买商品不存在');
            }

            $this->checkIsOpen($this->user->mall_id);
            \Yii::$app->setMall(Mall::findOne($this->user->mall_id));

            $this->getGoods($shoppingLikes->goods_id);
            $cats = $this->getGoodsCats();
            $attrList = $this->getGoodsAttrList();

            $selectAttr = [];
            $resetAttr = (new Goods())->resetAttr($this->goods->attr_groups);
            foreach ($this->goods->attr as $key => $aItem) {
                if ($key == 0) {
                    foreach ($resetAttr[$aItem['sign_id']] as $raItem) {
                        $selectAttr[] = [
                            'name' => $raItem['attr_group_name'],
                            'value' => $raItem['attr_name']
                        ];
                    }
                }
            }

            $picList = $this->getGoodsPicList();
            $postData = [
                "user_open_id" => $this->user->userInfo->platform_user_id,
                "sku_product_list" => [
                    [
                        "item_code" => $this->goods->sign . $this->goods->id,
                        "title" => $this->goods->name,
                        "desc" => "",
                        "category_list" => $cats ?: ['未知分类'],
                        "image_list" => $picList,
                        "src_wxapp_path" => "/pages/goods/goods?id=" . $this->goods->id,
                        "attr_list" => $attrList,
                        "update_time" => time(),
                        "sku_info" => [
                            "sku_id" => $this->goods->id,
                            "price" => $this->goods->price * 100,
                            "original_price" => $this->goods->goodsWarehouse->original_price * 100,
                            "status" => $this->goods->status ? 1 : 2,
                            "sku_attr_list" => $selectAttr,
                        ]
                    ]
                ]
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/addshoppinglist?access_token=" .
                \Yii::$app->wechat->getAccessToken(),
                $postData
            );

            $shoppingLikeUsers = ShoppingLikeUsers::find()->where([
                'user_id' => $userId, 'like_id' => $shoppingLikes->id
            ])->one();
            if (!$shoppingLikeUsers) {
                $shoppingLikeUsers = new ShoppingLikeUsers();
                $shoppingLikeUsers->user_id = $userId;
                $shoppingLikeUsers->like_id = $shoppingLikes->id;
                $res = $shoppingLikeUsers->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($shoppingLikeUsers));
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 删除 想买用户
     * @param $userId
     * @param $likeId
     * @return bool
     * @throws \Exception
     */
    public function destroyLikeUser($userId, $likeId)
    {
        try {
            if (!$userId) {
                throw new \Exception('userId不存在');
            }

            if (!$likeId) {
                throw new \Exception('likeId为空');
            }

            $this->getUser($userId);
            /** @var ShoppingLikes $shoppingLikes */
            $shoppingLikes = ShoppingLikes::find()->where([
                'id' => $likeId,
                'mall_id' => $this->user->mall_id,
                'is_delete' => 0,
            ])->one();
            if (!$shoppingLikes) {
                throw new \Exception('想买商品不存在');
            }

            $this->checkIsOpen($this->user->mall_id);
            \Yii::$app->setMall(Mall::findOne($this->user->mall_id));
            $this->getGoods($shoppingLikes->goods_id);

            $postData = [
                "user_open_id" => $this->user->userInfo->platform_user_id,
                "sku_product_list" => [
                    [
                        "item_code" => $this->goods->sign . $this->goods->id,
                        "sku_id" => $this->goods->sign . $this->goods->id,
                    ]
                ]
            ];

            $this->sendRequest(
                "https://api.weixin.qq.com/mall/deleteshoppinglist?access_token=" .
                \Yii::$app->wechat->getAccessToken(),
                $postData
            );

            $shoppingLikes = ShoppingLikes::find()->where([
                'goods_id' => $this->goods->id,
                'is_delete' => 0,
                'mall_id' => $this->user->mall_id
            ])->one();
            if (!$shoppingLikes) {
                throw new \Exception('无关联用户记录');
            }

            /** @var ShoppingLikeUsers $shoppingLikeUsers */
            $shoppingLikeUsers = ShoppingLikeUsers::find()->where([
                'like_id' => $shoppingLikes->id, 'user_id' => $this->user->id
            ])->one();
            if ($shoppingLikeUsers) {
                $shoppingLikeUsers->is_delete = 1;
                $res = $shoppingLikeUsers->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($shoppingLikeUsers));
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 获取商品信息
     * @return array
     */
    private function getGoodInfo()
    {
        $productInfo = [];
        foreach ($this->order->detail as $item) {
            $item->goods_info = \Yii::$app->serializer->decode($item->goods_info);

            // 商品规格信息
            $stockAttrInfo = [];
            foreach ($item->goods_info['attr_list'] as $alItem) {
                $stockAttrInfo[] = [
                    'attr_name' => [
                        'name' => $alItem['attr_group_name']
                    ],
                    'attr_value' => [
                        'name' => $alItem['attr_name']
                    ]
                ];
            }
            $cats = [];
            if (isset($item->goods)) {
                foreach ($item->goods->goodsWarehouse->cats as $cItem) {
                    $cats[] = $cItem->name;
                }
            }

            $itemList = [
                "item_code" => $this->order->sign . $item->goods_id,
                "sku_id" => $this->order->sign . $item->goods_id,
                "amount" => $item->num,
                "total_fee" => $item->total_price * 100,
                "thumb_url" => $item->goods_info['goods_attr']['pic_url'],
                "title" => $item->goods->goodsWarehouse->name ?: '未知商品',
                "desc" => "",
                "unit_price" => $item->goods->price * 100,
                "original_price" => $item->goods->goodsWarehouse->original_price * 100,
                "stock_attr_info" => $stockAttrInfo,
                "category_list" => $cats ?: ['未知分类'],
                "item_detail_page" => [
                    "path" => 'pages/goods/goods?id=' . $item->goods_id
                ],
            ];
            $productInfo[] = $itemList;
        }

        return $productInfo;
    }

    /**
     * 检测是否开启好物圈功能
     * @param $mallId
     * @return bool
     * @throws \Exception
     */
    private function checkIsOpen($mallId = null)
    {
        $headers = \Yii::$app->request->getHeaders();
        // 小程序端请求才需判断
        if (isset($headers['X-App-Platform'])) {
            // TODO 多商户没有设置功能
            $setting = ShoppingSetting::find()->where([
                'mall_id' => $mallId
            ])->one();
            if (!$setting || $setting['is_open'] != 1) {
                throw new \Exception('好物圈功能未开启');
            }

            // TODO 需排除支付宝插件
        }
        return true;
    }

    /**
     * 获取订单
     * @param $orderId
     * @throws \Exception
     */
    private function findOrder($orderId)
    {
        if (!$orderId) {
            throw new \Exception('订单ID为空');
        }

        $order = Order::find()->with('detail.goods.goodsWarehouse.cats')->where(['id' => $orderId])->one();
        if (!$order) {
            throw new \Exception('订单不存在');
        }
        $this->order = $order;
    }

    /**
     * 获取用户信息
     * @param null $userId
     * @throws \Exception
     */
    private function getUser($userId = null)
    {
        $user = User::find()->where([
            'id' => $userId ?: $this->order->user_id
        ])->with('userInfo')->one();

        if (!$user) {
            throw new \Exception('用户不存在');
        }

        $this->user = $user;
    }

    /**
     * 获取订单地址
     * @return array
     */
    private function getAddress()
    {
        return explode(' ', $this->order->address);
    }

    /**
     * 获取购买区域
     * @return string
     */
    private function getDistrict()
    {
        $address = $this->getAddress();
        $district = '';
        if (isset($address[2]) && isset($address[3])) {
            $district = $address[2] . $address[3];
        }

        return $district;
    }

    /**
     * 发送请求
     * @param $api
     * @param $postData
     * @throws \Exception
     */
    private function sendRequest($api, $postData)
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->post($api, json_encode($postData, JSON_UNESCAPED_UNICODE));
        $res = json_decode($curl->response, true);

        if ($res['errcode'] == 48001) {
            throw new \Exception('好物圈接口未授权！请至微信小程序后台申请');
        }

        if ($res['errcode'] != 0) {
            throw new \Exception('微信好物圈接口错误:' . $res['errmsg']);
        }

        \Yii::warning('微信好物圈执行完成');
    }

    /**
     * 获取商品
     * @param null $goodsId
     * @throws \Exception
     */
    private function getGoods($goodsId = null)
    {
        $goods = Goods::find()->with(['goodsWarehouse.cats', 'attr'])
            ->where(['id' => $goodsId])
            ->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        $this->goods = $goods;
    }

    /**
     * 获取商品分类
     * @return array
     */
    private function getGoodsCats()
    {
        $cats = [];
        foreach ($this->goods->goodsWarehouse->cats as $cItem) {
            $cats[] = $cItem->name;
        }

        return $cats;
    }

    /**
     * 获取商品所有规格列表
     * @return array
     */
    private function getGoodsAttrList()
    {
        $attrGroups = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = [];
        foreach ($attrGroups as $group) {
            foreach ($group['attr_list'] as $alItem) {
                $attrList[] = [
                    'name' => $group['attr_group_name'],
                    'value' => $alItem['attr_name']
                ];
            }
        }

        return $attrList;
    }

    /**
     * 获取已选商品规格
     * @param $attrId
     * @return array
     */
    private function getSelectGoodsAttr($attrId)
    {
        $selectAttr = [];
        $resetAttr = (new Goods())->resetAttr($this->goods->attr_groups);
        foreach ($this->goods->attr as $aItem) {
            if ($attrId == $aItem['id']) {
                foreach ($resetAttr[$aItem['sign_id']] as $raItem) {
                    $selectAttr[] = [
                        'name' => $raItem['attr_group_name'],
                        'value' => $raItem['attr_name']
                    ];
                }
            }
        }

        return $selectAttr;
    }

    /**
     * 获取商品图片列表
     * @return array
     */
    private function getGoodsPicList()
    {
        $picList = [];
        $picUrl = \Yii::$app->serializer->decode($this->goods->goodsWarehouse->pic_url);
        foreach ($picUrl as $pItem) {
            $picList[] = $pItem['pic_url'];
        }

        return $picList;
    }

    private function getOrderStatus()
    {
        $status = 3;// 支付完成
        if ($this->order->is_send == 1) {
            $status = 4;
        }
        if ($this->order->is_confirm == 1) {
            $status = 100;
        }
        if ($this->order->cancel_status == 1) {
            $status = 5;
        }

        return $status;
    }

    /**
     * @param Goods $goods
     * @return string
     */
    private function getGoodsPageUrl($goods)
    {
        if ($goods->sign == '' && $goods->mch_id == 0) {
            $path = 'pages/goods/goods?id=' . $goods->id;
        } elseif ($goods->sign == 'booking') {
            $path = 'plugins/book/goods/goods?goods_id=' . $goods->id;
        } elseif ($goods->sign == 'pintuan') {
            $path = 'plugins/pt/goods/goods?goods_id=' . $goods->id;
        } elseif ($goods->sign == 'miaosha') {
            $path = 'plugins/miaosha/goods/goods?id=' . $goods->id;
        } elseif ($goods->sign == 'bargain') {
            $path = 'plugins/bargain/goods/goods?goods_id=' . $goods->id;
        } elseif ($goods->mch_id > 0 || $goods->sign == 'mch') {
            $path = 'plugins/mch/goods/goods?id=' . $goods->id . '&mch_id=' . $goods->mch_id;
        } elseif ($goods->sign == 'step') {
            $path = 'plugins/step/goods/goods?goods_id=' . $goods->id;
        } elseif ($goods->sign == 'integral_mall') {
            $path = 'plugins/integral_mall/goods/goods?goods_id=' . $goods->id;
        } else {
            $path = 'pages/index/index';
        }
        return $path;
    }
}
