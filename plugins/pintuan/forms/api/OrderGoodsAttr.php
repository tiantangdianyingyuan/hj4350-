<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/8
 * Time: 17:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\pintuan\forms\api;

use app\forms\api\order\OrderException;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanGoodsShare;

/**
 * @property string $preferential_price
 * @property integer $pintuan_group_id
 * @property integer $pintuan_order_id
 * @property PintuanGoodsGroups $pintuanGroup
 * @property PintuanGoodsAttr $pintuanGoodsAttr
 */
class OrderGoodsAttr extends \app\forms\api\order\OrderGoodsAttr
{
    public $preferential_price;
    public $pintuan_group_id; // 阶梯团id 为0表示单独购买
    public $pintuan_order_id; // 阶梯团团长id 为0表示当前订单为团长订单
    public $pintuanGroup;
    public $pintuanGoodsAttr;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['preferential_price'], 'number']
        ]);
    }

    /**
     * @param PintuanGoodsAttr $pintuanGoodsAttr
     * @throws OrderException
     */
    public function setPintuanGoodsAttr($pintuanGoodsAttr)
    {
        if (!$pintuanGoodsAttr instanceof PintuanGoodsAttr) {
            throw new OrderException('参数$pintuanGoodsAttr必须是app\plugins\pintuan\models\PintuanGoodsAttr的一个实例');
        }

        $this->pintuanGoodsAttr = $pintuanGoodsAttr;
        $this->goodsAttr = $pintuanGoodsAttr->goodsAttr;
        $this->attributes = $pintuanGoodsAttr->goodsAttr->attributes;
        $this->price = $pintuanGoodsAttr->pintuan_price;
        $this->stock = $pintuanGoodsAttr->pintuan_stock;
        $this->original_price = $this->price;
        $this->discount = [];
        $this->attr_setting_type = $this->goods->attr_setting_type;
        $this->share_type = $this->goods->share_type;
        $this->individual_share = $this->goods->individual_share;

        if ($this->goods->attr_setting_type == 1) {
            // 详细设置
            $shareLevelList = PintuanGoodsShare::findAll([
                'goods_id' => $pintuanGoodsAttr->goods_id,
                'pintuan_goods_groups_id' => $this->pintuan_group_id,
                'pintuan_goods_attr_id' => $pintuanGoodsAttr->id,
                'is_delete' => 0,
            ]);
        } else {
            // 普通设置
            $shareLevelList = PintuanGoodsShare::findAll([
                'goods_id' => $pintuanGoodsAttr->goods_id,
                'pintuan_goods_groups_id' => $this->pintuan_group_id,
                'pintuan_goods_attr_id' => 0,
                'is_delete' => 0,
            ]);
        }
        foreach ($shareLevelList as $item) {
            $this->goods_share_level[] = [
                'share_commission_first' => $item->share_commission_first,
                'share_commission_second' => $item->share_commission_second,
                'share_commission_third' => $item->share_commission_third,
                'level' => $item->level,
            ];
        }
    }

    public function setGoodsAttrById($goodsAttrId)
    {
        if ($this->pintuan_group_id) {
            $preferentialPrice = 0;
            $discounts = [];
            // 拼团购买
            $pintuanGroupId = $this->pintuan_group_id;

            /* @var PintuanGoodsGroups $pintuanGoodsGroup */
            $pintuanGoodsGroup = PintuanGoodsGroups::find()->where([
                'id' => $pintuanGroupId,
                'is_delete' => 0
            ])->with(['attr.share', 'attr.memberPrice', 'attr.goodsAttr'])->one();
            if (!$pintuanGoodsGroup) {
                throw new OrderException('拼团组不存在');
            }

            if ($this->pintuan_order_id <= 0) {
                // 是否是团长
                $preferentialPrice = $pintuanGoodsGroup->preferential_price;
                $discounts[] = [
                    'name' => '团长优惠',
                    'value' => '-' . $preferentialPrice
                ];
            }

            /** @var PintuanGoodsAttr $pintuanGoodsAttr */
            $pintuanGoodsAttr = PintuanGoodsAttr::find()
                ->where([
                    'goods_attr_id' => $goodsAttrId,
                    'pintuan_goods_groups_id' => $this->pintuan_group_id
                ])
                ->with(['goodsAttr'])
                ->one();
            if (!$pintuanGoodsAttr) {
                throw new OrderException('无法查询到规格信息');
            }
            $this->pintuanGroup = $pintuanGoodsGroup;
            $this->setPintuanGoodsAttr($pintuanGoodsAttr);
            $this->discount = $discounts;
            $this->preferential_price = $preferentialPrice;
            $this->price = price_format($this->price - min($this->price, $preferentialPrice));
        } else {
            parent::setGoodsAttrById($goodsAttrId);
        }
    }

    // TODO 此方法废弃
    public function update($runValidation = true, $attributeNames = null)
    {
        if ($this->pintuan_group_id) {
            $this->pintuanGoodsAttr->pintuan_stock = $this->stock;
            return $this->pintuanGoodsAttr->update($runValidation, ['pintuan_stock']);
        } else {
            return parent::update($runValidation, $attributeNames);
        }
    }
}
