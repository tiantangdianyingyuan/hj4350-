<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 10:21
 */

namespace app\plugins\vip_card;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderSubmitForm;
use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\forms\mall\UserEditForm;
use app\plugins\vip_card\handlers\HandlerRegister;
use app\plugins\vip_card\handlers\OrderCreatedEventHandler;
use app\plugins\vip_card\handlers\OrderPayEventHandler;
use app\plugins\vip_card\handlers\OrderSalesEventHandler;
use app\plugins\vip_card\models\RemindTemplate;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardAppointGoods;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardDiscount;
use app\plugins\vip_card\models\VipCardSetting;
use app\plugins\vip_card\models\VipCardUser;
use yii\helpers\ArrayHelper;

class Plugin extends \app\plugins\Plugin
{
    private static $setting;
    private static $card;
    private static $vipUser;
    private static $user;
    private static $userInfo;

    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'route' => 'plugin/vip_card/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/vip_card/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '会员卡管理',
                'route' => 'plugin/vip_card/mall/card/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '编辑超级会员卡',
                        'route' => 'plugin/vip_card/mall/card/edit',
                    ],
                    [
                        'name' => '编辑超级会员子卡',
                        'route' => 'plugin/vip_card/mall/card/edit-detail',
                    ],
                    [
                        'name' => '编辑排序',
                        'route' => 'plugin/vip_card/mall/card/edit-sort',
                    ],
                    [
                        'name' => '开关',
                        'route' => 'plugin/vip_card/mall/card/switch-detail-status',
                    ],
                    [
                        'name' => '删除子卡',
                        'route' => 'plugin/vip_card/mall/card/detail-destroy',
                    ],
                ],
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/vip_card/mall/order/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '会员管理',
                'route' => 'plugin/vip_card/mall/user/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '添加新会员',
                        'route' => 'plugin/vip_card/mall/user/edit',
                    ],
                    [
                        'name' => '删除会员',
                        'route' => 'plugin/vip_card/mall/user/delete',
                    ],
                ],
            ],
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'vip_card';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '超级会员卡';
    }

    public function getIndexRoute()
    {
        return 'plugin/vip_card/mall/setting/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'vip_card',
                'name' => '超级会员卡',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-svip.png',
                'value' => '/plugins/vip_card/index/index',
            ],
        ];
    }

    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedEventHandler();
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayEventHandler();
    }

    public function getOrderSalesHandleClass()
    {
        return new OrderSalesEventHandler();
    }

    public function getOrderConfig()
    {
        $setting = (new CommonVipCardSetting())->getSetting();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_mail' => 1,
            'is_print' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    /**
     * @param $mchItem
     * @param bool $useTempVip 未开通超级会员情况下，模拟使用超级会员获取折扣金额
     * @param OrderSubmitForm $orderSubmitForm
     * @return mixed
     * @throws OrderException
     */
    public function vipDiscount($mchItem, $useTempVip = false, $orderSubmitForm = null)
    {
        $mchItem['has_vip_card'] = false; // 是否已经开通过超级会员卡

        if ($useTempVip) {
            //判断会员卡开关
            $model = VipCardSetting::findOne(['mall_id' => \Yii::$app->mall->id]);
            if (empty($model) || $model->is_vip_card == 0) {
                return $mchItem;
            }
        }

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /** @var UserIdentity $identity */
        $identity = $user->getIdentity()->andWhere(['is_delete' => 0])->one();
        if (!$identity) {
            return $mchItem;
        }
        $main = CommonVip::getCommon()->getMainCard();
        if (!$main) {
            return $mchItem;
        }
        $vip = VipCardUser::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['is_delete' => 0],
            ['user_id' => \Yii::$app->user->id],
            ['>', 'end_time', date('Y-m-d H:i:s')],
        ])->one();
        if (!$vip) {
            if (!$useTempVip) {
                return $mchItem;
            }
            $vip = new VipCardUser();
            $vip->end_time = date('Y-m-d H:i:s', time() + 86400);
            $vip->image_main_name = $main->name;
            $vip->image_discount = $main->discount;
            $vip->image_is_free_delivery = $main->is_free_delivery;
            $vip->image_type = $main->type;
            $vip->image_type_info = $main->type_info;
        } else {
            $mchItem['has_vip_card'] = true; // 未开通超级会员卡或已到期
        }
        if (strtotime($vip->end_time) < time()) {
            return $mchItem;
        }
        $mchItem['vip_discount'] = price_format(0);
        $totalSubPrice = 0; // 超级会员卡总计优惠金额
        foreach ($mchItem['goods_list'] as &$goodsItem) {

            if ($vip->image_is_free_delivery == 1) {
                if ($mchItem['express_price'] != 0) {
                    $mchItem['express_price_origin'] = $mchItem['express_price'];
                    $mchItem['express_price_desc'] = ($main->name ?? $vip->image_name) . '包邮-￥' . $mchItem['express_price'];
                    $mchItem['express_price'] = price_format(0);
                } else {
                    $mchItem['express_price_desc'] = '';
                }
            }

            $typeInfo = json_decode($vip->image_type_info, true);
            if (is_array($typeInfo) && !empty($typeInfo)) {
                if (!$orderSubmitForm->isGoodsEnableVipPrice($goodsItem)) {
                    continue;
                }
                $cat = GoodsCatRelation::find()->select('cat_id')->where([
                    'goods_warehouse_id' => $goodsItem['goods_warehouse_id'],
                    'is_delete' => 0,
                ])->all();
                $cats = array_column($cat, 'cat_id');
                $isInGoods = in_array($goodsItem['goods_warehouse_id'], $typeInfo['goods']);
                $isInCats = count(array_intersect($cats, $typeInfo['cats'])) > 0 ? true : false;
                $appoint = VipCardAppointGoods::find()->where(['goods_id' => $goodsItem['id']])->one();
                $isAppoint = !empty($appoint) ? true : false;
                $setting = $this->getRules();
                $isRule = in_array($goodsItem['sign'], $setting['rules']) ? true : false;

                if ((($typeInfo['all'] == true) || $isInGoods || $isInCats) && $isAppoint && $isRule) {
                    $vipUnitPrice = null;
                    $discountName = ($main->name ?? $vip->image_name) . '优惠';

                    $goodsItem['vip_discount'] = price_format(0);
                    if (!($vip->image_discount >= 0 && $vip->image_discount <= 10)) {
                        throw new OrderException('超级会员卡折扣率不合法，折扣率必须在0折~10折。');
                    }

                    //折上折
                    $vipSubPrice = $goodsItem['total_price'] * (1 - $vip->image_discount / 10);
                    if ($vipSubPrice != 0) {
                        // 减去超级会员卡优惠金额
                        $vipSubPrice = min($goodsItem['total_price'], $vipSubPrice);
                        $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $vipSubPrice);
                        $totalSubPrice += $vipSubPrice;
                        $goodsItem['discounts'][] = [
                            'name' => $discountName,
                            'value' => $vipSubPrice > 0 ?
                            ('-' . price_format($vipSubPrice))
                            : ('+' . price_format(0 - $vipSubPrice)),
                        ];
                        $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $vipSubPrice);
                        $goodsItem['vip_discount'] = price_format($vipSubPrice);
                    }
                }
            }
        }
        if ($totalSubPrice) {
            $mchItem['vip_discount'] = price_format($totalSubPrice);
        }
        return $mchItem;
    }

    public function getSetting()
    {
        return (new CommonVipCardSetting())->getSetting();
    }

    public function getCard()
    {
        $setting = $this->getRules();
        $rules = !empty($setting['rules']) ? $setting['rules'] : '';
        is_array($rules) && $rules[] = '';
        $card = CommonVip::getCommon()->getMainCard();
        if (empty($card)) {
            return [];
        }
        $card = ArrayHelper::toArray($card);
        $types = json_decode($card['type_info'], true);
        $card['type_info'] = $types;
        $goods = Goods::find()->select('goods_warehouse_id')->where(['id' => $types['goods'], 'sign' => ''])->all();
        $goodsIds = Goods::find()->alias('g')->select('g.id')->where([
            'g.goods_warehouse_id' => $goods,
            'g.sign' => $rules,
            'g.is_delete' => 0,
        ])->rightJoin(['ap' => VipCardAppointGoods::tableName()], "g.`id` = ap.`goods_id`")->all();
        $card['type_info']['goods'] = !empty($goodsIds) ? array_unique(array_column($goodsIds, 'id')) : [];
        return $card;
    }

    public function getCardDetail($id)
    {
        $model = VipCardDetail::findOne([
            'id' => $id,
            'is_delete' => 0,
            'status' => 0,
        ]);
        return $model;
    }

    public function getMyCard()
    {
        $card = VipCardUser::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->asArray()->one();
        if (empty($card)) {
            return [];
        }
        $setting = $this->getRules();
        $rules = !empty($setting['rules']) ? $setting['rules'] : '';
        is_array($rules) && $rules[] = '';

        $types = json_decode($card['image_type_info'], true);
        $card['image_type_info'] = $types;
        $goods = Goods::find()->select('goods_warehouse_id')->where(['id' => $types['goods'], 'sign' => ''])->all();
        $goodsIds = Goods::find()->alias('g')->select('g.id')->where([
            'g.goods_warehouse_id' => $goods,
            'g.sign' => $rules,
            'g.is_delete' => 0,
        ])->rightJoin(['ap' => VipCardAppointGoods::tableName()], "g.`id` = ap.`goods_id`")->all();
        $card['image_type_info']['goods'] = !empty($goodsIds) ? array_unique(array_column($goodsIds, 'id')) : [];
        return $card;
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    public function getUserInfo($user)
    {
        if (self::$userInfo) {
            $user = self::$userInfo;
        } else {
            $user = VipCardUser::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'user_id' => $user->id, 'is_delete' => 0])
                ->andWhere(['>', 'end_time', mysql_timestamp()])
                ->one();
            self::$userInfo = $user;
        }

        return [
            'is_vip_card_user' => isset($user->id) ? 1 : 0,
        ];
    }

    public function getAppConfig()
    {
        $p = 1;
        $errorMsg = '有会员卡权限';
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            $errorMsg = '无会员卡权限';
            $p = 0;
        }

        $setting = $this->getSetting();

        if ($setting['is_vip_card'] == 0) {
            $errorMsg = '会员卡插件已关闭';
            $p = 0;
        }

        $card = $this->getCard();
        if (!$card) {
            $errorMsg = '尚未添加会员卡';
            $p = 0;
        }

        $form = new CommonVipCardSetting();
        $setting = $form->getSetting();

        $return = [
            'setting' => $setting,
            'permission' => $p,
            'permission_msg' => $errorMsg,
        ];

        return $return;
    }

    public function getOrderInfo($orderId, $order)
    {
        try {
            $vip = VipCardDiscount::findOne(['order_id' => $orderId]);
            if (self::$card) {
                $main = self::$card;
            } else {
                $main = CommonVip::getCommon()->getMainCard();
                self::$card = $main;
            }
            if ($vip) {
                $name = !empty($vip->main_name) ? $vip->main_name : $main->name;
                $data = [
                    'discount_list' => [
                        'vip_discount' => [
                            'label' => $name . '优惠',
                            'value' => $vip->discount,
                        ],
                    ],
                    'print_list' => [
                        'vip_discount' => [
                            'label' => $name . '优惠',
                            'value' => $vip->discount,
                        ],
                    ],
                ];
                return $data;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAppoint($goods)
    {
        return CommonVip::getCommon()->getAppoint($goods);
    }

    public function getSmsSetting()
    {
        return [
            'vipCard' => [
                'title' => '超级会员卡续费短信通知',
                'content' => '例如：模板内容：您的超级会员卡即将到期，请及时续费，会员卡名称为${name}',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容: "您的超级会员卡即将到期，请及时续费，会员卡名称为${name}"，则需填写name',
                    ],
                ],
            ],
        ];
    }

    public function getGoodsExtra($goods)
    {
        return [
            'vip_card_appoint' => $this->getAppoint($goods),
        ];
    }

    public function templateList()
    {
        return [
            'vip_card_remind' => RemindTemplate::class,
        ];
    }

    public function getRules($plugins = ['advance', 'booking', 'exchange', 'gift', 'miaosha', 'pintuan', 'flash_sale'])
    {
        $list['rules'] = [''];
        foreach ($plugins as $item) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item);
                if (!method_exists($plugin, 'getEnableVipDiscount')) {
                    continue;
                }
                if ($plugin->getEnableVipDiscount()) {
                    $list['rules'][] = $plugin->getName();
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $list;
    }

    public function install()
    {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_vip_card_user` MODIFY COLUMN `all_send` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '所有赠送信息' AFTER `image_name`;
EOF;
        sql_execute($sql);
        return parent::update();
    }

    /**
     * 获取商品配置
     */
    public function getGoodsConfig()
    {
        return CommonVip::getCommon()->getGoodsConfig();
    }

    /**
     * @param $params
     * @return array
     * @throws \Exception
     * 获取商品信息
     */
    public function getGoodsPlugin($params)
    {
        $detail['is_vip_card_goods'] = 0;
        $appoint = VipCardAppointGoods::find()->where(['goods_id' => $params['goods']->id])->one();
        if ($appoint) {
            $detail['is_vip_card_goods'] = 1;
        }
        return $detail;
    }

    public function getOrderAction($actionList, $order)
    {
        $actionList['is_show_comment'] = 0;
        return $actionList;
    }

    /**
     * 新增或者续费超级会员卡用户
     * @param $detail_id int 子卡id
     * @param $user_id int 赠送的用户id
     * @param $sign string 赠送的插件标识
     * @param $payType
     * @param $isRecordOrder
     * @return array
     */
    public function becomeOrRenew($detail_id, $user_id, $sign, $payType, $isRecordOrder)
    {
        \Yii::$app->user->setIdentity(User::findOne($user_id));
        $form = new UserEditForm();
        $form->detail_id = $detail_id;
        $form->user_id = $user_id;
        $form->sign = $sign;
        $form->payType = $payType;
        $form->isRecordOrder = $isRecordOrder;
        return $form->save();
    }
}
