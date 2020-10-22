<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/28
 * Time: 15:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;


use app\forms\common\CommonMallMember;
use app\models\Form;
use app\models\FreeDeliveryRules;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsShare;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PostageRules;
use yii\helpers\ArrayHelper;

class CommonGoods extends Model
{
    private static $instance;
    public $mch_id;

    public static function getCommon()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $id
     * @param $asArray
     * @return array|\yii\db\ActiveRecord|null|Goods
     * 通过指定id获取Goods对象
     */
    public function getGoods($id, $asArray = false)
    {
        if ($this->mch_id) {
            $mchId = $this->mch_id;
        } elseif (\Yii::$app->mchId) {
            $mchId = \Yii::$app->mchId;
        } elseif (isset(\Yii::$app->user->identity) && \Yii::$app->user->identity->mch_id > 0) {
            $mchId = \Yii::$app->user->identity->mch_id;
        } else {
            $mchId = 0;
        }

        $query = Goods::find()->with(['attr.share', 'attr.memberPrice', 'attr.shareLevel', 'goodsWarehouse.cats'])
            ->with(['services' => function ($query) {
                $query->via->andWhere(['is_delete' => 0]);
            }])
            ->with(['cards' => function ($query) {
                $query->via->andWhere(['is_delete' => 0]);
            }])
            ->with(['coupons' => function ($query) {
                $query->via->andWhere(['is_delete' => 0]);
            }])
            ->where(['id' => $id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'mch_id' => $mchId]);
        if (isset(\Yii::$app->user->identity) && \Yii::$app->user->identity->mch_id > 0) {
            $query->with('mchGoods');
        }

        return $query->asArray($asArray)->one();
    }

    /**
     * @param $goodsId
     * @param $asArray
     * @return array|\yii\db\ActiveRecord|MallGoods|null
     * 通过指定的goods_d获取MallGoods对象
     */
    public function getMallGoods($goodsId, $asArray = false)
    {
        $mallGoods = MallGoods::find()->where([
            'goods_id' => $goodsId, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id
        ])->asArray($asArray)->one();
        return $mallGoods;
    }

    public function getGoodsShare($goodsId, $asArray = false)
    {
        $goodsShare = GoodsShare::find()->select([
            'share_commission_first', 'share_commission_second', 'share_commission_third', 'level'
        ])->where(['goods_id' => $goodsId, 'goods_attr_id' => 0, 'is_delete' => 0])
            ->asArray($asArray)->all();
        return $goodsShare;
    }

    /**
     * @param $id
     * @param $asArray
     * @return array|\yii\db\ActiveRecord|null|GoodsWarehouse
     * 通过指定id获取Goods对象
     */
    public function getGoodsWarehouse($id, $asArray = false)
    {
        $goods = GoodsWarehouse::find()->where(['id' => $id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->asArray($asArray)->one();

        return $goods;
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     * 获取goods商品详情
     */
    public function getGoodsDetail($id, $hasGoodsWarehouse = true)
    {
        $goods = $this->getGoods($id);

        if (!$goods) {
            throw new \Exception('数据异常,该条数据不存在');
        }
        $detail = ArrayHelper::toArray($goods);
        if (isset($goods->goodsWarehouse) && $goods->goodsWarehouse) {
            $detail = array_merge(ArrayHelper::toArray($goods->goodsWarehouse), $detail);
        }
        $detail = array_merge($detail, $this->transformAttr($goods));
        if ($hasGoodsWarehouse) {
            $goodsInfo = $goods->goodsWarehouse->goodsInfo;
        } else {
            $goodsInfo = $goods;
        }
        $attrGroups = \Yii::$app->serializer->decode($goodsInfo->attr_groups);
        $newAttrList = [];
        foreach ($detail['attr'] as $item) {
            $attr = '';
            foreach ($item['attr_list'] as $value) {
                $attr .= $value['attr_group_name'] . ":" . $value['attr_name'] . ';';
            }
            $newAttrList[] = $attr;
        }
        $detail['goods_warehouse'] = [
            'goods_id' => $goodsInfo->id,
            'name' => $goods->goodsWarehouse->name,
            'original_price' => $goods->goodsWarehouse->original_price,
            'type' => $goods->goodsWarehouse->type,
            'attr_groups' => $attrGroups,
            'attr_list' => $newAttrList,
        ];

        // 商品分类
        $cats = [];
        foreach ($goods->goodsWarehouse->cats as $item) {
            $cats[$item['id']]['label'] = $item['name'];
            $cats[$item['id']]['value'] = $item['id'];
        }
        $detail['cats'] = array_values($cats);

        // 商品分类
        $mchCats = [];
        foreach ($goods->goodsWarehouse->mchCats as $item) {
            $mchCats[$item['id']]['label'] = $item['name'];
            $mchCats[$item['id']]['value'] = $item['id'];
        }
        $detail['mchCats'] = array_values($mchCats);

        // 商品服务
        $newServices = [];
        foreach ($goods->services as $item) {
            $newServices[] = $item;
        }
        $detail['services'] = $newServices;

        // 商品卡券
        $newCards = [];
        foreach ($goods->goodsCardRelation as $item) {
            if ($item->goodsCards->is_delete !== 0) {
                continue;
            }
            $carts = \yii\helpers\ArrayHelper::toArray($item->goodsCards);
            $carts['num'] = $item->num;
            $newCards[] = $carts;
        }
        $detail['cards'] = $newCards;

        //商品优惠券
        $newCoupons = [];
        foreach ($goods->goodsCouponRelation as $item) {
            if ($item->goodsCoupons->is_delete !== 0) {
                continue;
            }
            $coupons = \yii\helpers\ArrayHelper::toArray($item->goodsCoupons);
            $coupons['num'] = $item->num;
            $newCoupons[] = $coupons;
        }
        $detail['coupons'] = $newCoupons;

        $detail['pic_url'] = json_decode($goods->goodsWarehouse->pic_url, true) ?: [];

        $detail['area_limit'] = \yii\helpers\BaseJson::decode($goods->area_limit) ?: [['list' => []]];
        $goodsShare = $this->getGoodsShare($goods->id, true);
        if ($goodsShare) {
            $detail['shareLevelList'] = $goodsShare;
        }

        // 运费可能被删除、再查询一次
        $postageRule = PostageRules::findOne(['is_delete' => 0, 'id' => $detail['freight_id']]);
        $detail['freight_id'] = $postageRule ? $postageRule->id : 0;
        $detail['freight'] = $postageRule ? $postageRule : ['id' => 0, 'name' => '默认运费'];

        $freeDeliveryRule = FreeDeliveryRules::findOne(['id' => $detail['shipping_id'], 'is_delete' => 0]);
        $detail['shipping_id'] = $freeDeliveryRule ? $freeDeliveryRule->id : 0;
        $detail['shipping'] = $freeDeliveryRule ? $freeDeliveryRule : ['id' => 0, 'name' => '默认包邮规则'];

        if ($detail['form_id'] > 0) {
            $detail['form'] = Form::find()->where([
                'mall_id' => $goods->mall_id, 'is_delete' => 0, 'id' => $detail['form_id']
            ])->select('id,name')->one();
        } else {
            $detail['form'] = $detail['form_id'] < 0 ? null : ['id' => 0, 'name' => '默认表单'];
        }

        unset($detail['id']);

        //图片替换
        $temp = [];
        foreach ($detail['attr'] as $v) {
            foreach ($v['attr_list'] as $w) {
                if (!isset($temp[$w['attr_id']])) {
                    $temp[$w['attr_id']] = $v['pic_url'];
                }
            }
        }

        foreach ($detail['attr_groups'] as $k => $v) {
            foreach ($v['attr_list'] as $l => $w) {
                $detail['attr_groups'][$k]['attr_list'][$l]['pic_url'] = $temp[$w['attr_id']] ?? "";
            }
        }

        if ($detail['use_attr'] === 0) {
            $detail['attr_default_name'] = $detail['attr_groups'][0]['attr_list'][0]['attr_name'];
        } else {
            $detail['attr_default_name'] = '';
        }

        // 获取插件中商品信息
        $detail['plugin_data'] = [];
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        foreach ($permission as $item) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item);
            } catch (\Exception $exception) {
                continue;
            }
            if (!method_exists($plugin, 'getGoodsPlugin')) {
                continue;
            }
            $params = [
                'goodsWarehouse' => $goods->goodsWarehouse,
                'goods' => $goods
            ];
            $detail['plugin_data'][$item] = $plugin->getGoodsPlugin($params);
        }

        if (isset(\Yii::$app->user->identity) && \Yii::$app->user->identity->mch_id > 0) {
            $detail['sort'] = $goods->mchGoods->sort;
        }
        if ($detail['guarantee_title'] == '') {
            $detail['guarantee_title'] = "商城基础保障";
        }
        if ($detail['guarantee_pic'] == '') {
            $detail['guarantee_pic'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/statics/img/mall/goods/guarantee/goods-pic.png";
        }
        return $detail;
    }

    /**
     * 转换商品规格格式
     * @param $goods Goods
     * @return mixed
     * @throws \Exception
     */
    public function transformAttr($goods)
    {
        if (!isset($goods->attr) || !$goods->attr) {
            throw new \Exception('商品规格信息有误');
        }
        $detail = [];

        $attrGroups = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = $goods->resetAttr($attrGroups);

        $goodsNum = 0;
        $mallMembers = CommonMallMember::getAllMember();
        /* @var GoodsAttr $attrItem */
        foreach ($goods->attr as $key => $attrItem) {
            $detail['attr'][$key] = ArrayHelper::toArray($attrItem);
            $detail['attr'][$key]['attr_list'] = $attrList[$attrItem['sign_id']];

            $result = [];
            foreach ($attrItem->memberPrice as $item) {
                $result['level' . $item['level']] = $item['price'];
            }
            foreach ($mallMembers as $member) {
                if (isset($result['level' . $member['level']]) === false) {
                    $result['level' . $member['level']] = 0;
                }
            }
            $detail['attr'][$key]['member_price'] = $result;
            $shareLevel = [];
            if ($attrItem->shareLevel) {
                foreach ($attrItem->shareLevel as $share) {
                    $shareLevel[] = [
                        'level' => $share->level,
                        'share_commission_first' => $share->share_commission_first,
                        'share_commission_second' => $share->share_commission_second,
                        'share_commission_third' => $share->share_commission_third,
                    ];
                }
            }
            $detail['attr'][$key]['shareLevelList'] = $shareLevel;
            if ($goods->use_attr == 0) {
                // 默认规格
                $detail['member_price'] = $result;
                $goodsNum = $attrItem['stock'];
                $detail['goods_no'] = $attrItem['no'];
                $detail['goods_weight'] = $attrItem['weight'];
            } else {
                // 多规格商品 总库存是根据规格库存相加
                $goodsNum += $attrItem['stock'];
            }
        }
        $detail['goods_num'] = $goodsNum;
        $detail['attr_groups'] = $attrGroups;

        return $detail;
    }

    /**
     * @param $attrGroups
     * @return array
     * 改变规格选择 例如砍价的
     */
    public function changeAttr($attrGroups)
    {
        if (!is_array($attrGroups)) {
            return [];
        }
        $newList = [];
        foreach ($attrGroups as $item) {
            $newItem = [
                'attr_group_name' => $item['attr_group_name'],
                'attr_name' => $item['attr_list'][0]['attr_name'],
            ];
            $newList[] = $newItem;
        }
        return $newList;
    }

    /**
     * @param Order $order
     * @param string $type 枚举值sub|add
     * @throws \Exception
     */
    public function setGoodsPayment($order, $type = 'sub')
    {
        if ($order->is_pay != 1) {
            \Yii::warning('未支付订单不需要进行商品支付信息处理');
            return true;
        }
        $goodsIdList = array_column($order->detail, 'goods_id');
        /* @var OrderDetail[] $list */
        $list = OrderDetail::find()->alias('od')->where(['od.goods_id' => $goodsIdList, 'od.is_delete' => 0])
            ->leftJoin(['o' => Order::tableName()], 'o.id = od.order_id')
            ->andWhere(['o.user_id' => $order->user_id, 'o.is_pay' => 1])
            ->andWhere(['!=', 'od.order_id', $order->id])
            ->groupBy('od.goods_id')
            ->all();
        $goodsList = [];
        foreach ($order->detail as $detail) {
            if (!isset($goodsList[$detail->goods_id])) {
                $goodsList[$detail->goods_id] = [
                    'goods' => $detail->goods,
                    'num' => 0,
                    'total_price' => 0,
                    'goods_id' => $detail->goods_id
                ];
            }
            $goodsList[$detail->goods_id]['num'] += $detail->num;
            $goodsList[$detail->goods_id]['total_price'] += floatval($detail->total_price);
        }
        $goodsList = array_values($goodsList);
        foreach ($goodsList as $value) {
            $flag = true;
            foreach ($list as $item) {
                if ($item->goods_id == $value['goods_id']) {
                    $flag = false;
                    break;
                }
            }
            /* @var Goods $goods */
            $goods = $value['goods'];
            if ($type == 'sub') {
                if ($flag) {
                    $goods->payment_people -= min($goods->payment_people, 1);
                }
                $goods->payment_num -= min($goods->payment_num, $value['num']);
                $goods->payment_order -= min($goods->payment_order, 1);
                $goods->payment_amount -= min($goods->payment_amount, floatval($value['total_price']));
            } elseif ($type == 'add') {
                if ($flag) {
                    $goods->payment_people += 1;
                }
                $goods->payment_num += $value['num'];
                $goods->payment_order += 1;
                $goods->payment_amount += floatval($value['total_price']);
            } else {
                throw new \Exception('错误的type值');
            }
            $goods->save();
        }
    }

    /**
     * @param Order $order
     * @return boolean
     * 更新商品销量
     */
    public function setGoodsSales($order)
    {
        try {
            $goodsList = array_column($order->detail, 'goods');
            $this->setGoodsListSales($goodsList);
        } catch (\Exception $exception) {
            \Yii::warning('更新商品销量出错');
            \Yii::warning($exception);
        }
        return true;
    }

    /**
     * @param Goods[] $goodsList
     * @return boolean
     */
    public function setGoodsListSales($goodsList)
    {
        $goodsIdList = array_column($goodsList, 'id');
        $orderIds = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->andWhere([
            'or',
            ['is_pay' => 1],
            ['pay_type' => 2]
        ])->andWhere(['!=', 'cancel_status', 1])
            ->select('id');

        $sales = OrderDetail::find()->where(['goods_id' => $goodsIdList, 'order_id' => $orderIds])
            ->select(['IF(sum(num), sum(num), 0) as count', 'goods_id'])
            ->asArray()
            ->groupBy('goods_id')
            ->all();
        $sales = array_combine(array_column($sales, 'goods_id'), array_column($sales, 'count'));
        $ignore = [];
        foreach ($goodsList as $goods) {
            if (in_array($goods->id, $ignore)) {
                continue;
            }
            $goods->sales = (isset($sales[$goods->id]) && $sales[$goods->id]) ? $sales[$goods->id] : 0;
            $goods->save();
        }
        return true;
    }
}
