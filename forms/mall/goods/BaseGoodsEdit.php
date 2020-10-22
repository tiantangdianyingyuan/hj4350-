<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 15:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;

use app\events\GoodsEvent;
use app\forms\common\CommonMallMember;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCardRelation;
use app\models\GoodsCouponRelation;
use app\models\GoodsMemberPrice;
use app\models\GoodsServiceRelation;
use app\models\GoodsShare;
use app\models\GoodsWarehouse;
use app\models\MallMembers;
use app\models\Model;
use app\plugins\mch\models\Mch;
use yii\db\Exception;

/**
 * @property Goods $goods
 * @property GoodsWarehouse $goodsWarehouse
 */
abstract class BaseGoodsEdit extends Model
{
    public $id;
    public $goods_warehouse_id;
    public $status;
    public $price;
    public $use_attr;
    public $attr;
    public $goods_num;
    public $virtual_sales;
    public $cats;
    public $mchCats;
    public $services;
    public $goods_no;
    public $goods_weight;
    public $sort;
    public $is_level;
    public $confine_count;
    public $give_integral;
    public $give_integral_type;
    public $give_balance;
    public $give_balance_type;
    public $forehead_integral;
    public $forehead_integral_type;
    public $accumulative;
    public $is_negotiable;
    public $freight_id;
    public $shipping_id;
    public $pieces;
    public $forehead;
    public $app_share_title;
    public $app_share_pic;
    public $pic_url;
    public $is_area_limit;
    public $area_limit;
    public $attr_default_name;
    public $confine_order_count;

    //分销
    public $individual_share;
    public $share_type;
    public $shareLevelList;
    public $rebate;
    public $attr_setting_type;
    public $cards;
    public $coupons;
    public $attrGroups;

    public $isNewRecord;
    public $goods;
    public $goodsWarehouse;
    public $member_price;
    public $diffAttrIds = [];

    public $is_level_alone;
    public $is_default_services;
    public $select_attr_groups;
    public $form_id;

    public $mch_id;

    protected $newAttrs;
    protected $sign;
    /** @var  Mch */
    protected $mch;

    public $is_vip_card_goods;
    public $plugin_data;
    public $guarantee_title;
    public $guarantee_pic;

    private $maxNum = 9999999;

    public function rules()
    {
        return [
            [['status', 'use_attr', 'goods_num', 'price'], 'required'],
            [['use_attr', 'goods_num', 'virtual_sales', 'goods_weight', 'individual_share',
                'share_type', 'attr_setting_type', 'sort', 'is_level',
                'confine_count', 'give_integral', 'give_integral_type', 'forehead_integral_type',
                'accumulative', 'freight_id', 'shipping_id', 'pieces', 'is_level_alone', 'is_default_services',
                'goods_warehouse_id', 'mch_id', 'form_id', 'is_area_limit', 'confine_order_count',
                'is_vip_card_goods', 'give_balance_type'], 'integer'],
            [['goods_no', 'rebate', 'app_share_title', 'app_share_pic', 'attr_default_name',
                'guarantee_title', 'guarantee_pic'], 'string'],
            [['forehead', 'id'], 'number'],
            [['cats', 'mchCats', 'services', 'cards', 'attr', 'attrGroups', 'member_price',
                'select_attr_groups', 'shareLevelList', 'plugin_data', 'coupons'], 'safe'],
            [['virtual_sales', 'freight_id', 'is_level', 'is_level_alone', 'forehead', 'forehead_integral',
                'give_integral', 'individual_share', 'is_level_alone', 'pieces', 'share_type', 'accumulative',
                'attr_setting_type', 'goods_weight', 'is_area_limit', 'form_id',
                'give_balance', 'shipping_id'], 'default', 'value' => 0],
            [['app_share_title', 'app_share_pic', 'attr_default_name', 'guarantee_title',
                'guarantee_pic'], 'default', 'value' => ''],
            [['sort'], 'default', 'value' => 100],
            [['sort'], 'integer', 'max' => 9999999],
            [['area_limit'], 'trim'],
            [['confine_count', 'confine_order_count'], 'default', 'value' => -1],
            [['forehead_integral_type', 'give_integral_type', 'is_level',
                'is_default_services', 'give_balance_type'], 'default', 'value' => 1],
            [['price', 'forehead_integral', 'give_integral', 'give_balance'], 'number', 'min' => 0],
            [['price', 'forehead_integral'], 'number', 'max' => 99999999],
            [['price', 'pieces', 'forehead', 'give_integral', 'give_balance', 'forehead_integral', 'confine_count',
                'confine_order_count', 'goods_weight', 'virtual_sales'], 'number', 'max' => 9999999]
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '商品上架状态',
            'price' => '商品售价',
            'use_attr' => '是否使用规格',
            'attr' => '商品规格',
            'goods_num' => '商品总库存',
            'goods_weight' => '商品重量',
            'virtual_sales' => '已出售量',
            'sort' => '排序',
            'is_level' => '是否会员价购买',
            'is_level_alone' => '是否单独设置会员价格',
            'app_share_title' => '自定义分享标题',
            'app_share_pic' => '自定义分享图片',
            'pieces' => '单品满件包邮',
            'forehead' => '单品满额包邮',
            'give_integral' => '积分赠送',
            'forehead_integral' => '积分抵扣',
            'confine_count' => '限购数量（商品）',
            'confine_order_count' => '限购数量（订单）',
            'give_balance' => '余额赠送'
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!parent::validate($attributeNames, $clearErrors)) {
            return false;
        }
        try {
            $this->attrValidator();
            $this->attrGroupNameValidator();
            return true;
        } catch (\Exception $exception) {
            $this->addError('attr', $exception->getMessage());
            return false;
        }
    }

    /**
     * 规格名称特殊符验证
     */
    protected function attrGroupNameValidator()
    {
        $preg = "/[\'=]|\\\|\"|\|/";

        if ((int)$this->use_attr === 1 && !$this->attrGroups) {
            throw new Exception('请添加规格组信息');
        }

        $arrGroups = [];
        foreach ($this->attrGroups as $item) {
            if (!trim($item['attr_group_name'])) {
                throw new \Exception('规则组名称不能为空');
            }

            if (preg_match($preg, $item['attr_group_name'])) {
                throw new Exception('商品规格组、规格名称、规格详情不能包含 \' " \\ = 等特殊符');
            }

            if (!isset($item['attr_list']) || count($item['attr_list']) == 0) {
                throw new Exception('请完善规格组（' . $item['attr_group_name'] . '）的规格值');
            }
            // 规格组名称 不能重复
            if (in_array(trim($item['attr_group_name']), $arrGroups)) {
                throw new \Exception('规格组名称不能重复');
            }
            $arrGroups[] = trim($item['attr_group_name']);

            $arrAttr = [];
            foreach ($item['attr_list'] as $item2) {
                if (!trim($item2['attr_name'])) {
                    throw new \Exception('规则值名称不能为空');
                }

                if (preg_match($preg, $item2['attr_name'])) {
                    throw new Exception('商品规格组、规格名称、规格详情不能包含 \' " \\ = 等特殊符');
                }

                if (in_array(trim($item2['attr_name']), $arrAttr)) {
                    throw new \Exception('同一规格组下,规格名称不能重复');
                }
                $arrAttr[] = trim($item2['attr_name']);
            }
        }
    }


    /**
     * 商品规格数据验证
     */
    protected function attrValidator()
    {
        $allMembers = CommonMallMember::getAllMember();
        $memberList = ['level0'];
        /** @var MallMembers $item */
        foreach ($allMembers as $item) {
            $memberList[] = 'level' . $item->level;
        }
        // 多规格检查
        if ((int)$this->use_attr === 1) {
            if (!$this->attr || !is_array($this->attr)) {
                throw new Exception('请完善商品规格信息');
            }

            $goodsNum = 0;
            $num = $this->maxNum;
            foreach ($this->attr as $k => $item) {
                if ($item['stock'] > $num) {
                    throw new \Exception('商品库存不能大于' . $num);
                }
                if ($item['price'] > $num) {
                    throw new \Exception('商品价格不能大于' . $num);
                }
                if ($item['weight'] > $num) {
                    throw new \Exception('商品重量不能大于' . $num);
                }
                // 多规格计算商品库存总数
                $goodsNum += (int)$item['stock'];

                if (!isset($item['stock']) || (int)$item['stock'] < 0) {
                    throw new Exception('规格库存必须大于0');
                }
                if (!isset($item['price']) || (int)$item['price'] < 0) {
                    throw new Exception('规格价格必须大于0');
                }
                if ((int)$item['weight'] < 0) {
                    throw new Exception('规格重量不能小于0');
                }
                if (mb_strlen($item['no']) > 60) {
                    throw new \Exception('货号不能越过60个字符');
                }


                $this->checkExtra($item);

                // 没有会员价时、不需要验证会员价
                if (isset($item['member_price']) && $this->is_level_alone == 1) {
                    foreach ($item['member_price'] as $key => $memberItem) {
                        if (!isset($memberList[$key])) {
                            continue;
                        }

                        if ((int)$memberItem < 0) {
                            throw new Exception('多规格会员价不能小于0');
                        }
                        if ((doubleval($memberItem)) > doubleval($item['price'])) {
                            throw new \Exception('会员价不能大于商品售价');
                        }
                    }
                }
            }

            if ($goodsNum > $num) {
                throw new Exception('商品总库存的值必须不大于' . $this->maxNum);
            }
        } else {
            $this->attr_setting_type = 0;
            if ($this->goods_num > $this->maxNum) {
                throw new \Exception('商品总库存不能大于' . $this->maxNum);
            }

            if ($this->goods_weight > $this->maxNum) {
                throw new \Exception('商品重量不能大于' . $this->maxNum);
            }
        }

        // 默认规格下会员价检查
        if ((int)$this->use_attr === 0 && (int)$this->is_level === 1) {
            foreach ($this->member_price as $key => $item) {
                if (!isset($memberList[$key])) {
                    continue;
                }
                if ($item < 0) {
                    throw new Exception('会员价不能小于0');
                }

                if (doubleval($item) > doubleval($this->price)) {
                    throw new \Exception('会员价不能大于商品售价');
                }
            }
        }
    }

    abstract public function save();

    abstract protected function setGoodsSign();

    /**
     * @return Goods
     */
    protected function getGoods()
    {
        return $this->goods;
    }

    /**
     * @throws \Exception
     * 设置商品
     */
    protected function setGoods()
    {
        $this->handleAttrGroups();
        $this->setMch();

        $common = CommonGoods::getCommon();
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先选择商品');
        }
        $goodsWarehouse = $common->getGoodsWarehouse($this->goods_warehouse_id);
        if (!$goodsWarehouse) {
            throw new \Exception('商品以删除，请重新选择商品');
        }
        if ($this->id) {
            $this->isNewRecord = false;
            $goods = $this->getGoodsData($common);
        } else {
            $goods = new Goods();
            $goods->mall_id = \Yii::$app->mall->id;
            $goods->is_delete = 0;
            $this->isNewRecord = true;
        }
        $this->goodsWarehouse = $goodsWarehouse;
        if ($goodsWarehouse->type == 'ecard') {
            $this->is_area_limit = 0;
            $this->area_limit = [['list' => []]];
            if ($this->use_attr != 0) {
                throw new \Exception('卡密类商品不使用选择规格');
            }
        }

        // 商品
        $goods->goods_warehouse_id = $this->goods_warehouse_id;
        $goods->virtual_sales = $this->virtual_sales;
        $goods->price = $this->price;
        $goods->use_attr = $this->use_attr;
        $goods->attr_groups = \Yii::$app->serializer->encode($this->attrGroups);
        $goods->app_share_title = $this->app_share_title;
        $goods->app_share_pic = $this->app_share_pic;
        $goods->status = $this->status;
        $goods->confine_count = $this->confine_count;
        $goods->confine_order_count = $this->confine_order_count;
        $goods->pieces = $this->pieces;
        $goods->forehead = $this->forehead;
        $goods->freight_id = $this->freight_id;
        $goods->shipping_id = $this->shipping_id;
        $goods->give_integral = $this->give_integral;
        $goods->give_integral_type = $this->give_integral_type;
        $goods->give_balance = $this->give_balance;
        $goods->give_balance_type = $this->give_balance_type;
        $goods->forehead_integral = $this->forehead_integral;
        $goods->forehead_integral_type = $this->forehead_integral_type;
        $goods->accumulative = $this->accumulative;
        $goods->individual_share = $this->individual_share;
        $goods->attr_setting_type = $this->attr_setting_type;
        $goods->form_id = $this->form_id;
        $goods->is_area_limit = $this->is_area_limit;
        $goods->area_limit = \Yii::$app->serializer->encode($this->area_limit);
        $goods->guarantee_title = $this->guarantee_title;
        $goods->guarantee_pic = $this->guarantee_pic;
        if ($this->mch_id) {
            $goods->is_level = 0;
        } else {
            $goods->is_level = $this->is_level;
            $goods->sort = $this->sort;
        }
        $goods->is_level_alone = $this->is_level_alone;
        $goods->share_type = $this->share_type;
        $goods->sign = $this->setGoodsSign();
        $goods->mch_id = $this->mch_id;
        $goods->is_default_services = $this->is_default_services;
        $res = $goods->save();

        if (!$res) {
            throw new Exception($this->getErrorMsg($goods));
        }

        $this->setExtraGoods($goods);

        $this->goods = $goods;
    }


    protected function getGoodsData($common)
    {
        /** @var CommonGoods $common */
        $common->mch_id = $this->mch_id;
        $goods = $common->getGoods($this->id);
        if (!$goods) {
            throw new \Exception('goods商品不存在或以删除');
        }

        return $goods;
    }

    /**
     * 商品规格设置
     * @throws Exception
     */
    protected function setAttr()
    {
        if ((int)$this->use_attr === 0) {
            // 未使用规格就添加默认规格
            $this->setDefaultAttr();
            $attrPicList = [];
        } else {
            $this->handleAttr();
            // 多规格数据处理
            $this->newAttrs = $this->attr;
            $attrPicList = array_column($this->attrGroups[0]['attr_list'], 'pic_url', 'attr_id');
        }

        $oldAttr = GoodsAttr::find()
            ->where(['is_delete' => 0, 'goods_id' => $this->goods->id])
            ->select('id')
            ->asArray()
            ->all();

        // 是否为新增
        if (!$this->isNewRecord) {
            GoodsAttr::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
            GoodsShare::updateAll(['is_delete' => 1], ['is_delete' => 0, 'goods_id' => $this->goods->id]);
            GoodsMemberPrice::updateAll(['is_delete' => 1], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        }
        if ($this->attr_setting_type == 0) {
            // 普通设置
            // 默认分销价的商品规格ID为 0
            $this->setGoodsShare(0, $this->shareLevelList);
            $this->checkMchSharePrice($this->shareLevelList, $this->price, $this->share_type);
        }

        // 旧规格ID
        $oldAttrIds = [];
        $newAttrIds = [];
        foreach ($oldAttr as $oldItem) {
            $oldAttrIds[] = $oldItem['id'];
        }

        $goodsStock = 0;
        foreach ($this->newAttrs as $newAttr) {
            $goodsStock += $newAttr['stock'];

            // 记录规格ID数组
            $signIds = '';
            foreach ($newAttr['attr_list'] as $aLItem) {
                $signIds .= $signIds ? ':' . (int)$aLItem['attr_id'] : (int)$aLItem['attr_id'];
            }

            // TODO 待修改
            // 判断规格是需要新增还是更新
            $goodsAttr = null;
            if ($this->goods->id) {
                $goodsAttr = GoodsAttr::findOne([
                    'id' => isset($newAttr['id']) ? $newAttr['id'] : 0,
                    'goods_id' => $this->goods->id
                ]);
            }
            if ($goodsAttr) {
                $goodsAttr->is_delete = 0;
            } else {
                $goodsAttr = new GoodsAttr();
            }
            if ($newAttr['stock'] > 9999999) {
                throw new \Exception('商品总库存不能大于9999999');
            }
            $goodsAttr->goods_id = $this->goods->id;
            $goodsAttr->sign_id = $signIds;
            $goodsAttr->stock = $newAttr['stock'];
            $goodsAttr->price = $newAttr['price'];
            $goodsAttr->no = $newAttr['no'];
            $goodsAttr->weight = $newAttr['weight'] ?: 0;
            //$goodsAttr->pic_url = $newAttr['pic_url'];
            $key = strstr($signIds, ':', true) ?: $signIds;
            $goodsAttr->pic_url = $attrPicList[$key] ?? '';

            $res = $goodsAttr->save();
            $newAttrIds[] = $goodsAttr->id;
            if (!$res) {
                throw new Exception($this->getErrorMsg($goodsAttr));
            }

            $diffAttrIds = array_diff($oldAttrIds, $newAttrIds);
            $this->diffAttrIds = count($diffAttrIds) ? $oldAttrIds : $diffAttrIds;
            /**
             * 开放自定义处理规格接口
             */
            $this->setExtraAttr($goodsAttr, $newAttr);

            if ($this->attr_setting_type == 1) {
                if ($this->use_attr == 1) {
                    // 详细设置
                    $this->setGoodsShare($goodsAttr->id, $newAttr['shareLevelList']);
                    $this->checkMchSharePrice($newAttr['shareLevelList'], $newAttr['price'], $this->share_type);
                } else {
                    // 详细设置(不适用规格)
                    $this->setGoodsShare($goodsAttr->id, $this->shareLevelList);
                    $this->checkMchSharePrice($this->shareLevelList, $newAttr['price'], $this->share_type);
                }
            }
            if (isset($newAttr['member_price'])) {
                foreach ($newAttr['member_price'] as $memberPriceKey => $memberPriceItem) {
                    // 例如键值为 `level1` 去除`level`后就是会员等级
                    $memberLevel = (int)substr($memberPriceKey, 5);
                    // 设置会员价
                    $this->setGoodsMemberPrice($goodsAttr->id, $memberLevel, $memberPriceItem);
                }
            }
        }
        if ($goodsStock > 9999999) {
            throw new \Exception('商品总库存不能大于9999999');
        }
        $this->goods->goods_stock = $goodsStock;
        $res = $this->goods->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($this->goods));
        }
    }

    /**
     * @throws Exception
     * 添加默认规格
     */
    protected function setDefaultAttr()
    {
        if ($this->select_attr_groups) {
            $attrList = $this->select_attr_groups;
        } else {
            $attrList = [
                [

                    'attr_group_name' => '规格',
                    'attr_group_id' => 1,
                    'attr_id' => 1,
                    'attr_name' => $this->attr_default_name ?: '默认',
                ]
            ];
        }
        $count = 1;
        $attrGroups = [];
        foreach ($attrList as &$item) {
            $item['attr_group_id'] = $count;
            $count++;
            $item['attr_id'] = $count;
            $count++;
            $newItem = [
                'attr_group_id' => $item['attr_group_id'],
                'attr_group_name' => $item['attr_group_name'],
                'attr_list' => [
                    [
                        'attr_id' => $item['attr_id'],
                        'attr_name' => $item['attr_name']
                    ]
                ]
            ];
            $attrGroups[] = $newItem;
        }
        unset($item);
        // 未使用规格 就添加一条默认规格
        $newAttrs = [
            [
                'attr_list' => $attrList,
                'stock' => $this->goods_num,
                'price' => $this->price,
                'no' => $this->goods_no ? $this->goods_no : '',
                'weight' => $this->goods_weight ? $this->goods_weight : 0,
                'pic_url' => '',
            ]
        ];

        // 未使用规格情况下，要把上一次的规格ID 存回去，不然规格记录会重复添加
        if (count($this->attr) === 1 && isset($this->attr[0]['id'])) {
            $newAttrs[0]['id'] = $this->attr[0]['id'];
        }

        $this->goods->attr_groups = \Yii::$app->serializer->encode($attrGroups);
        $res = $this->goods->save();
        if (!$res) {
            throw new Exception($this->getErrorMsg($this->goods));
        }


        // 将会员价格式调整为 key|value 即 会员等级|会员价
        $memberPrices = $this->member_price;
        $newMemberPrice = [];
        foreach ($memberPrices as $key => $memberPrice) {
            $newMemberPrice[$key] = $memberPrice;
        }
        $newAttrs[0]['member_price'] = $newMemberPrice;
        $this->newAttrs = $newAttrs;
    }

    /**
     * @throws Exception
     * 设置卡券数据
     */
    protected function setCard()
    {
        GoodsCardRelation::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        if ($this->cards && is_array($this->cards)) {
            foreach ($this->cards as $k => $item) {
                /* @var GoodsCardRelation $goodsCardRelation */
                $goodsCardRelation = GoodsCardRelation::findOne(['goods_id' => $this->goods->id, 'card_id' => $item['id']]);
                if ($goodsCardRelation) {
                    $goodsCardRelation->is_delete = 0;
                    $goodsCardRelation->num = $item['num'];
                } else {
                    $goodsCardRelation = new GoodsCardRelation();
                    $goodsCardRelation->goods_id = $this->goods->id;
                    $goodsCardRelation->card_id = $item['id'];
                    $goodsCardRelation->num = $item['num'];
                }
                $res = $goodsCardRelation->save();

                if (!$res) {
                    throw new Exception($this->getErrorMsg($goodsCardRelation));
                }
            }
        }
    }

    /**
     * @throws Exception
     * 设置优惠券数据
     */
    protected function setCoupon()
    {
        GoodsCouponRelation::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        if ($this->coupons && is_array($this->coupons)) {
            foreach ($this->coupons as $k => $item) {
                /* @var GoodsCouponRelation $goodsCouponRelation */
                $goodsCouponRelation = GoodsCouponRelation::findOne(['goods_id' => $this->goods->id, 'coupon_id' => $item['id']]);
                if ($goodsCouponRelation) {
                    $goodsCouponRelation->is_delete = 0;
                    $goodsCouponRelation->num = $item['num'];
                } else {
                    $goodsCouponRelation = new GoodsCouponRelation();
                    $goodsCouponRelation->goods_id = $this->goods->id;
                    $goodsCouponRelation->coupon_id = $item['id'];
                    $goodsCouponRelation->num = $item['num'];
                }
                $res = $goodsCouponRelation->save();

                if (!$res) {
                    throw new Exception($this->getErrorMsg($goodsCouponRelation));
                }
            }
        }
    }

    /**
     * @throws Exception
     * 设置商品服务
     */
    protected function setGoodsService()
    {
        // 添加服务
        GoodsServiceRelation::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        if ($this->services && is_array($this->services)) {
            foreach ($this->services as $item) {
                /* @var GoodsServiceRelation $goodsServiceRelation */
                $goodsServiceRelation = GoodsServiceRelation::findOne([
                    'goods_id' => $this->goods->id,
                    'service_id' => $item['id'],
                ]);

                if ($goodsServiceRelation) {
                    $goodsServiceRelation->is_delete = 0;
                } else {
                    $goodsServiceRelation = new GoodsServiceRelation();
                    $goodsServiceRelation->service_id = $item['id'];
                    $goodsServiceRelation->goods_id = $this->goods->id;
                }
                $res = $goodsServiceRelation->save();

                if (!$res) {
                    throw new Exception($this->getErrorMsg($goodsServiceRelation));
                }
            }
        }
    }

    /**
     * @param $goodsAttrId
     * @param array $shareLevelList
     * @return boolean
     * @throws \Exception
     */
    protected function setGoodsShare($goodsAttrId, $shareLevelList)
    {
        if (!is_array($shareLevelList) || $this->individual_share == 0) {
            return false;
        }

        $list = [];
        if (!$this->isNewRecord) {
            $res = GoodsShare::find()
                ->where(['goods_id' => $this->goods->id, 'goods_attr_id' => $goodsAttrId, 'is_delete' => 0])
                ->all();
            /* @var GoodsShare[] $res */
            foreach ($res as $item) {
                $item->is_delete = 1;
                $list[$item->level] = $item;
            }
        }

        /* @var GoodsShare[] $list */
        foreach ($shareLevelList as $shareLevel) {
            if (!isset($list[$shareLevel['level']])) {
                $goodsShare = new GoodsShare();
                $goodsShare->is_delete = 0;
                $goodsShare->goods_id = $this->goods->id;
                $goodsShare->goods_attr_id = $goodsAttrId;
            } else {
                $goodsShare = $list[$shareLevel['level']];
                $goodsShare->is_delete = 0;
            }
            $model = new GoodsShareForm();
            $model->attributes = $shareLevel;
            if (!$model->validate()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            $goodsShare->share_commission_first = $model->share_commission_first;
            $goodsShare->share_commission_second = $model->share_commission_second;
            $goodsShare->share_commission_third = $model->share_commission_third;
            $goodsShare->level = $model->level;
            $list[$shareLevel['level']] = $goodsShare;
        }
        foreach ($list as $item) {
            if (!$item->save()) {
                throw new \Exception($this->getErrorMsg($item));
            }
        }
        return true;
    }

    /**
     * @param integer $goodsAttrId 商品规格
     * @param integer $level 会员等级
     * @param int $price
     * @throws Exception
     */
    private function setGoodsMemberPrice($goodsAttrId, $level, $price = 0)
    {
        if (!$price) {
            $price = 0;
        }

        $goodsMemberPrice = GoodsMemberPrice::findOne(['goods_attr_id' => $goodsAttrId, 'level' => $level,
            'goods_id' => $this->goods->id, 'is_delete' => 0]);
        // 更新|新增
        if (!$goodsMemberPrice) {
            $goodsMemberPrice = new GoodsMemberPrice();
            $goodsMemberPrice->goods_id = $this->goods->id;
            $goodsMemberPrice->goods_attr_id = $goodsAttrId;
            $goodsMemberPrice->is_delete = 0;
        }

        $goodsMemberPrice->level = $level;
        $goodsMemberPrice->price = floatval($price);
        $res = $goodsMemberPrice->save();
        if (!$res) {
            throw new Exception($this->getErrorMsg($goodsMemberPrice));
        }
    }

    /**
     * 多商户商品分销价 总和不能大于商品销售价 + 商城提成
     * @param array $shareLevelList
     * @param $goodsPrice
     * @param $shareType
     * @throws \Exception
     */
    private function checkMchSharePrice($shareLevelList, $goodsPrice, $shareType)
    {
        if (\Yii::$app->mchId) {
            if (empty($shareLevelList)) {
                return;
            }
            $first = max(0, max(array_column($shareLevelList, 'share_commission_first')));
            $second = max(0, max(array_column($shareLevelList, 'share_commission_second')));
            $third = max(0, max(array_column($shareLevelList, 'share_commission_third')));
            // 商城提成
            $deductMoney = $goodsPrice * ($this->mch->transfer_rate / 1000);
            // 分销佣金类型。0.固定 | 1.百分比
            if ($shareType == 1) {
                $newFirst = $goodsPrice * ($first / 100);
                $newSecond = $goodsPrice * ($second / 100);
                $newThird = $goodsPrice * ($third / 100);
                $shareMoney = $newFirst + $newSecond + $newThird;
            } else {
                // 分销总金额
                $shareMoney = $first + $second + $third;
            }
            $moneyCount = price_format($deductMoney + $shareMoney);

            if ($moneyCount > $goodsPrice) {
                throw new \Exception('分销佣金不能大于商品金额');
            }
        }
    }

    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        return true;
    }

    protected function setExtraGoods($goods)
    {
        return true;
    }

    protected function checkExtra($goodsAttr)
    {
        return true;
    }

    private function handleAttrGroups()
    {
        $this->attrGroups = $this->addAttrGroupsId($this->attrGroups);
    }

    private function handleAttr()
    {
        foreach ($this->attr as &$item) {
            foreach ($item['attr_list'] as &$alItem) {
                $alItem['attr_group_id'] = $this->newAttrGroupList[$alItem['attr_group_name']];
                $alItem['attr_id'] = $this->newAttrList[$alItem['attr_name']];
            }
            unset($alItem);
        }
        unset($item);
    }

    private $newAttrGroupList = [];
    private $newAttrList = [];
    private $signArr = [];

    private function addAttrGroupsId($list, &$id = 1)
    {
        $newId = 1;
        foreach ($list as $key => $item) {
            if (isset($item['attr_list'])) {
                $this->newAttrGroupList[$item['attr_group_name']] = $newId;
                $list[$key]['attr_group_id'] = $newId++;
                $newItemList = $this->addAttrGroupsId($item['attr_list'], $id);
                $list[$key]['attr_list'] = $newItemList;
            } else {
                if (isset($this->signArr[$item['attr_name']])) {
                    $this->newAttrList[$item['attr_name']] = $this->signArr[$item['attr_name']];
                    $list[$key]['attr_id'] = $this->signArr[$item['attr_name']];
                } else {
                    $this->signArr[$item['attr_name']] = $id;
                    $this->newAttrList[$item['attr_name']] = $id;
                    $list[$key]['attr_id'] = $id++;
                }
            }
        }
        return $list;
    }

    private function setMch()
    {
        if (!$this->mch_id) {
            if (\Yii::$app->mchId) {
                $this->mch_id = \Yii::$app->mchId;
            }
            if (isset(\Yii::$app->user->identity) && \Yii::$app->user->identity->mch_id > 0) {
                $this->mch_id = \Yii::$app->user->identity->mch_id;
            }
        }

        if ($this->mch_id) {
            $this->mch = Mch::findOne($this->mch_id);
        } else {
            $this->mch_id = 0;
        }
    }

    /**
     * 触发商品编辑事件
     * @param bool $isVipCardGoods
     * @param bool $diffAttrIds
     */
    protected function setListener($isVipCardGoods = true, $diffAttrIds = true)
    {
        $event['goods'] = $this->goods;
        $diffAttrIds && $event['diffAttrIds'] = $this->diffAttrIds;
        $isVipCardGoods && $event['isVipCardGoods'] = $this->is_vip_card_goods;
        \Yii::$app->trigger(Goods::EVENT_EDIT, new GoodsEvent($event));
    }
}
