<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 15:00
 */

namespace app\plugins\pintuan\forms\common;

use app\forms\common\CommonMallMember;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\models\GoodsServices;
use app\models\MallMembers;
use app\models\OrderDetail;
use app\models\ShareSetting;
use app\plugins\pintuan\models\Goods;
use yii\base\BaseObject;

/**
 * @property Goods $goods
 */
class CommonGoodsDetail extends BaseObject
{
    public $goods;
    public $attr;

    /**
     * @return array
     * 获取规格
     */
    public function getAttr()
    {
        $attrGroup = \Yii::$app->serializer->decode($this->goods['attr_groups']);
        $attrList = (new Goods())->resetAttr($attrGroup);
        $newAttr = [];
        foreach ($this->attr as $key => $item) {
            $item['attr_list'] = $attrList[$item['sign_id']];
            $newAttr[] = $item;
        }

        if ($this->goods['is_level'] == 1) {
            $newAttr = $this->getMemberPrice($newAttr);
        }
        return $newAttr;
    }

    /**
     * @return int
     * 获取商品库存
     */
    public function getGoodsNum()
    {
        $attr = $this->attr;
        $goodNumCount = 0;
        foreach ($attr as $item) {
            $goodNumCount += $item['stock'];
        }
        return $goodNumCount;
    }

    /**
     * @return string|null
     * 获取第一个规格的货号
     */
    public function getGoodsNo()
    {
        $attr = $this->attr;
        foreach ($attr as $index => $item) {
            return $item['no'];
        }
        return null;
    }

    /**
     * @return string|null
     * 获取第一个规格的重量
     */
    public function getGoodsWeight()
    {
        $attr = $this->attr;
        foreach ($attr as $index => $item) {
            return $item['weight'];
        }
        return null;
    }

    /**
     * @return mixed|string
     * 获取商品最低价
     */
    public function getPriceMin()
    {
        $attr = $this->attr;
        $price = $this->goods['price'];
        foreach ($attr as $index => $item) {
            $price = min($price, $item['price']);
        }
        return round($price, 2);
    }

    /**
     * @return mixed|string
     * 获取商品最高价
     */
    public function getPriceMax()
    {
        $attr = $this->attr;
        $price = $this->goods['price'];
        foreach ($attr as $index => $item) {
            $price = max($price, $item['price']);
        }
        return round($price, 2);
    }

    /**
     * @return array
     * 获取商品服务
     */
    public function getServices()
    {
        $services = [];
        $defaultService = $this->goods['services'];
        /* @var $defaultService GoodsServices[] */
        foreach ($defaultService as $item) {
            $services[] = $item['name'];
        }

        return $services;
    }

    /**
     * @return array
     * 获取卡券列表
     */
    public function getCards()
    {
        $cards = [];
        $defaultCards = $this->goods['cards'];
        /* @var $defaultCards GoodsCards[] */
        foreach ($defaultCards as $item) {
            $cards[] = [
                'card_id' => $item['id'],
                'name' => $item['name'],
                'description' => $item['description']
            ];
        }

        return $cards;
    }

    public function getMemberPrice($attr)
    {
        /* @var MallMembers[] $members */
        $members = CommonMallMember::getAllMember();
        /* @var GoodsAttr[] $goodsAttr */
        $goodsAttr = $this->attr;
        $memberList = [];
        foreach ($members as $member) {
            foreach ($goodsAttr as $value) {
                $newItem = [
                    'discount' => $member->discount,
                    'level' => $member->level,
                    'goods_attr_id' => null,
                    'price' => 0
                ];
                if ($value['memberPrice']) {
                    foreach ($value['memberPrice'] as $memberPrice) {
                        if ($memberPrice['level'] == $member->level) {
                            $newItem['goods_attr_id'] = $value['id'];
                            $newItem['price'] = $memberPrice['price'];
                            $memberList[] = $newItem;
                        }
                    }
                } else {
                    $memberList[] = $newItem;
                }
            }
        }

        foreach ($attr as &$item) {
            $first = true;
            foreach ($memberList as $index => $value) {
                if ($value['goods_attr_id']) {
                    if ($item['id'] == $value['goods_attr_id']) {
                        if ($this->goods['is_level_alone'] == 1) {
                            $item['member_price_' . $value['level']] = $value['price'];
                        } else {
                            $item['member_price_' . $value['level']] = $value['discount'] * $item['price'] / 10;
                        }
                    } else {
                        continue;
                    }
                } else {
                    $item['member_price_' . $value['level']] = $value['discount'] * $item['price'] / 10;
                }
                if ($first) {
                    $first = false;
                    $item['price_member'] = $item['member_price_' . $value['level']];
                }
            }
            unset($item['memberPrice']);
        }
        return $attr;
    }

    public function getShare()
    {
        // 商城是否开启分销
        $shareSetting = ShareSetting::getList($this->goods['mall_id']);
        if (!$shareSetting) {
            return 0;
        }
        if ($shareSetting[ShareSetting::LEVEL] == 0) {
            return 0;
        }
        $share = 0;
        // 是否单独设置分销
        if ($this->goods['individual_share'] == 1) {
            $first = 0;
            // 是否详细设置分销
            if ($this->goods['attr_setting_type'] == 1) {
                foreach ($this->attr as $item) {
                    $first = max($first, $item['share']['share_commission_first']);
                }
            } else {
                foreach ($this->goods['share'] as $item) {
                    if ($item['goods_attr_id'] == 0) {
                        $first = $item['share_commission_first'];
                        break;
                    }
                }
            }
        } else {
            $first = $shareSetting[ShareSetting::FIRST];
        }

        // 分销佣金是百分比还是固定金额
        if ($this->goods['share_type'] == 0) {
            $share = price_format($first, 'float', 2);
        } else {
            $priceMax = $this->getPriceMax();
            $share = price_format($priceMax * $first / 100, 'float', 2);
        }
        return $share;
    }

    public function getSales()
    {
        /* @var OrderDetail[] $orderDetailList */
        $orderDetailList = OrderDetail::find()->where(['is_delete' => 0, 'goods_id' => $this->goods['id']])
            ->with('refund')->all();

        $sales = $this->goods['virtual_sales'];
        foreach ($orderDetailList as $orderDetail) {
            if ($orderDetail->refund && $orderDetail->refund->status == 1 && $orderDetail->refund->type == 1 && $orderDetail->refund->is_confirm == 1) {
                continue;
            }
            $sales += $orderDetail->num;
        }
        return $sales;
    }
}
