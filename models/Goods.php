<?php

namespace app\models;

use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGoods;
use Yii;

/**
 * This is the model class for table "{{%goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $goods_warehouse_id
 * @property int $status 上架状态：0=下架，1=上架
 * @property string $price 售价
 * @property int $use_attr 是否使用规格：0=不使用，1=使用
 * @property string $attr_groups 商品规格组
 * @property int $virtual_sales 已出售量
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $sort 排序
 * @property int $goods_stock 商品总库存
 * @property int $confine_count 购物数量限制
 * @property int $shipping_id 包邮模板ID
 * @property int $freight_id 运费模板ID
 * @property int $give_integral 赠送积分
 * @property int $give_integral_type 赠送积分类型1.固定值 |2.百分比
 * @property int $give_balance 赠送余额
 * @property int $give_balance_type 赠送余额类型1.固定值 |2.百分比
 * @property int $forehead_integral 可抵扣积分
 * @property int $forehead_integral_type 可抵扣积分类型1.固定值 |2.百分比
 * @property int $accumulative 允许多件累计折扣
 * @property int $individual_share 是否单独分销设置：0=否，1=是
 * @property int $attr_setting_type 分销设置类型 0.普通设置|1.详细设置
 * @property int $is_level 是否享受会员价购买
 * @property int $is_level_alone 是否单独设置会员价
 * @property int $share_type 佣金配比 0--固定金额 1--百分比
 * @property string $sign 商品标示用于区分商品属于什么模块
 * @property string $app_share_pic 自定义分享图片
 * @property string $app_share_title 自定义分享标题
 * @property int $is_default_services 默认服务 0.否|1.是
 * @property int $payment_people 支付人数
 * @property int $payment_num 支付件数
 * @property string $payment_amount 支付金额
 * @property int $payment_order 支付订单数
 * @property int $form_id 自定义表单id  0--表示默认表单 -1--表示不使用表单
 * @property int $confine_order_count
 * @property int $is_area_limit 是否单独区域购买
 * @property string $area_limit
 * @property int $sales 商品实际销量
 * @property GoodsShare[] $share
 * @property GoodsAttr[] $attr
 * @property GoodsCards[] $cards
 * @property Coupon[] $coupons
 * @property GoodsServices[] $services
 * @property GoodsWarehouse $goodsWarehouse
 * @property MallGoods $mallGoods
 * @property MchGoods $mchGoods
 * @property Mch $mch
 *
 * @property string $name 商品名称
 * @property string $subtitle 副标题
 * @property string $originalPrice 原价
 * @property string $costPrice 成本价
 * @property string $detail 商品详情，图文
 * @property string $coverPic 商品缩略图
 * @property string $picUrl 商品轮播图
 * @property string $videoUrl 商品视频
 * @property string $unit 单位
 * @property string $pageUrl 商品跳转链接
 * @property GoodsShare $shareLevel
 * @property GoodsCardRelation $goodsCardRelation
 * @property int $detail_count 商品详情浏览量
 * @property GoodsCouponRelation $goodsCouponRelation
 * @property string $guarantee_title 商品服务标题
 * @property string $guarantee_pic 商品服务标识
 */
class Goods extends ModelActiveRecord
{
    // 商品编辑事件
    const EVENT_EDIT = 'goodsEdit';
    // 商品删除事件
    const EVENT_DESTROY = 'goodsDestroy';
    // 商品上下架事件
    const EVENT_STATUS = 'goodsStatus';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_warehouse_id', 'attr_groups', 'freight_id', 'created_at', 'updated_at',
                'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'goods_warehouse_id', 'status', 'use_attr', 'goods_stock', 'virtual_sales',
                'confine_count', 'shipping_id', 'freight_id', 'give_integral', 'give_integral_type',
                'forehead_integral_type', 'accumulative', 'individual_share', 'attr_setting_type', 'is_level',
                'is_level_alone', 'share_type', 'is_default_services', 'sort', 'is_delete', 'payment_people',
                'payment_num', 'payment_order', 'confine_order_count', 'is_area_limit', 'form_id', 'sales', 'detail_count', 'give_balance_type'], 'integer'],
            [['price', 'forehead', 'forehead_integral', 'payment_amount', 'give_balance'], 'number'],
            [['attr_groups', 'area_limit'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['sign', 'app_share_pic'], 'string', 'max' => 255],
            [['app_share_title'], 'string', 'max' => 65],
            [['guarantee_title'], 'string', 'max' => 64],
            [['guarantee_pic'], 'string', 'max' => 2048],
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
            'mch_id' => 'Mch ID',
            'goods_warehouse_id' => 'Goods Warehouse ID',
            'status' => '上架状态：0=下架，1=上架',
            'price' => '售价',
            'use_attr' => '是否使用规格：0=不使用，1=使用',
            'attr_groups' => '商品规格组',
            'virtual_sales' => '已出售量',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'sort' => '排序',
            'goods_stock' => '商品总库存',
            'confine_count' => '购物数量限制',
            'shipping_id' => '包邮模板ID',
            'freight_id' => '运费模板ID',
            'give_integral' => '赠送积分',
            'give_integral_type' => '赠送积分类型1.固定值 |2.百分比',
            'give_balance' => '赠送余额',
            'give_balance_type' => '赠送余额类型1.固定值 |2.百分比',
            'forehead_integral' => '可抵扣积分',
            'forehead_integral_type' => '可抵扣积分类型1.固定值 |2.百分比',
            'accumulative' => '允许多件累计折扣',
            'individual_share' => '是否单独分销设置：0=否，1=是',
            'attr_setting_type' => '分销设置类型 0.普通设置|1.详细设置',
            'is_level' => '是否享受会员价购买',
            'is_level_alone' => '是否单独设置会员价',
            'share_type' => '佣金配比 0--固定金额 1--百分比',
            'sign' => '商品标示用于区分商品属于什么模块',
            'app_share_pic' => '自定义分享图片',
            'app_share_title' => '自定义分享标题',
            'is_default_services' => '默认服务 0.否|1.是',
            'payment_people' => '支付人数',
            'payment_num' => '支付件数',
            'payment_amount' => '支付金额',
            'payment_order' => '支付订单数',
            'form_id' => '自定义表单id  0--表示默认表单 -1--表示不使用表单',
            'confine_order_count' => '限单数量',
            'sales' => '商品实际销量',
            'detail_count' => '商品详情浏览量',
            'guarantee_title' => '商品服务标题',
            'guarantee_pic' => '商品服务标识',
        ];
    }

    public function getServices()
    {
        return $this->hasMany(GoodsServices::className(), ['id' => 'service_id'])
            ->viaTable(GoodsServiceRelation::tableName(), ['goods_id' => 'id'], function ($query) {
                $query->andWhere(['is_delete' => 0]);
            })->andWhere(['is_delete' => 0])->orderBy(['sort' => SORT_ASC]);
    }

    public function getCards()
    {
        return $this->hasMany(GoodsCards::className(), ['id' => 'card_id'])
            ->viaTable(GoodsCardRelation::tableName(), ['goods_id' => 'id'], function ($query) {
                $query->andWhere(['is_delete' => 0]);
            })->andWhere(['is_delete' => 0]);
    }

    public function getGoodsCardRelation()
    {
        return $this->hasMany(GoodsCardRelation::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getCoupons()
    {
        return $this->hasMany(Coupon::className(), ['id' => 'coupon_id'])
            ->viaTable(GoodsCouponRelation::tableName(), ['goods_id' => 'id'], function ($query) {
                $query->andWhere(['is_delete' => 0]);
            })->andWhere(['is_delete' => 0]);
    }

    public function getGoodsCouponRelation()
    {
        return $this->hasMany(GoodsCouponRelation::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'id'])->andWhere([
            'is_delete' => 0,
        ]);
    }

    public function getCart()
    {
        return $this->hasMany(Cart::className(), ['goods_id' => 'id']);
    }

    public function getMch()
    {
        return $this->hasOne(Mch::className(), ['id' => 'mch_id']);
    }

    /**
     * 将规格组重新排列组合成一维数组
     * 键名为 attr_id:attr_id 例如 1:2
     * @param $arr null|string|array
     * @return array
     */
    public function resetAttr($arr = null)
    {
        if (!$arr) {
            $arr = Yii::$app->serializer->decode($this->attr_groups);
        }
        if (is_string($arr)) {
            $arr = Yii::$app->serializer->decode($arr);
        }
        if (!(is_array($arr) || is_object($arr))) {
            $arr = Yii::$app->serializer->decode($arr);
        }
        $result = [];
        foreach ($arr as $item) {
            $newItem = [];
            $result2 = [];
            foreach ($item['attr_list'] as $value) {
                $result1 = [];
                $str = $value['attr_id'];
                $newItem[$str] = [
                    [
                        'attr_group_name' => $item['attr_group_name'],
                        'attr_group_id' => $item['attr_group_id'],
                        'attr_id' => $value['attr_id'],
                        'attr_name' => $value['attr_name'],
                    ]
                ];
                if (count($result) > 0) {
                    foreach ($result as $key2 => $item2) {
                        $str2 = $key2 . ':' . $str;
                        $result1[$str2] = array_merge($item2, $newItem[$str]);
                    }
                    $result2 = array_merge($result2, $result1);
                } else {
                    $result2 = $newItem;
                }
            }
            $result = $result2;
        }
        return $result;
    }

    /**
     * 单个sing_id获取相应规格
     * @param $signId
     * @param null|string|array $arr
     * @return mixed
     */
    public function signToAttr($signId, $arr = null)
    {
        $attrGroupList = $this->resetAttr($arr);
        return $attrGroupList[$signId];
    }

    public function getShare()
    {
        return $this->hasMany(GoodsShare::className(), ['goods_id' => 'id'])->where(['is_delete' => 0, 'level' => 0]);
    }

    public function getMchGoods()
    {
        return $this->hasOne(MchGoods::className(), ['goods_id' => 'id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id']);
    }

    public function getMallGoods()
    {
        return $this->hasOne(MallGoods::className(), ['goods_id' => 'id']);
    }

    public function getName()
    {
        return $this->goodsWarehouse->name;
    }

    public function getSubtitle()
    {
        return $this->goodsWarehouse->subtitle;
    }

    public function getOriginalPrice()
    {
        return $this->goodsWarehouse->original_price;
    }

    public function getCostPrice()
    {
        return $this->goodsWarehouse->cost_price;
    }

    public function getDetail()
    {
        return $this->goodsWarehouse->detail;
    }

    public function getCoverPic()
    {
        return $this->goodsWarehouse->cover_pic;
    }

    public function getPicUrl()
    {
        return $this->goodsWarehouse->pic_url;
    }

    public function getVideoUrl()
    {
        return $this->goodsWarehouse->video_url;
    }

    public function getUnit()
    {
        return $this->goodsWarehouse->unit;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @return string
     */
    public function getPageUrl()
    {
        try {
            if ($this->mch_id) {
                $plugins = \Yii::$app->plugin->getPlugin('mch');
            } elseif ($this->sign) {
                $plugins = \Yii::$app->plugin->getPlugin($this->sign);
            } else {
                throw new \Exception('商城商品');
            }
            if (!method_exists($plugins, 'getGoodsUrl')) {
                throw new \Exception('不存在getGoodsUrl方法');
            }
            $pageUrl = $plugins->getGoodsUrl(['id' => $this->id, 'mch_id' => $this->mch_id]);
        } catch (\Exception $exception) {
            $pageUrl = '/pages/goods/goods?id=' . $this->id;
        }
        return $pageUrl;
    }

    public function getShareLevel()
    {
        return $this->hasMany(GoodsShare::className(), ['goods_id' => 'id'])->where(['is_delete' => 0]);
    }
}
