<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/8
 * Time: 10:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\order;


use app\forms\common\ecard\CommonEcard;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsShare;
use app\models\Model;

/**
 * @property int $id
 * @property int $goods_id
 * @property string $sign_id 规格ID标识
 * @property int $stock 库存
 * @property string $original_price 商品原价格
 * @property string $price 商品实际价格
 * @property string $no 货号
 * @property int $weight 重量（克）
 * @property string $pic_url 规格图片
 * @property int $individual_share 是否单独分销设置：0=否，1=是
 * @property int $attr_setting_type 是否详细设置：0=否，1=是
 * @property int $goods_share_level 分销等级设置
 * @property int $share_type 佣金配比 0--固定金额 1--百分比
 * @property array $extra
 * @property string $member_price 会员价
 * @property string $integral_price 积分抵扣金额
 * @property string $use_integral 抵扣的积分
 * @property array $discount 优惠措施
 * @property int $goods_warehouse_id 商品库ID
 * @property string $name 商品库名称
 * @property string $cover_pic 商品库缩略图
 * @property string $detail 商品详情
 * @property string $pic_list 商品轮播图
 * @property int $number 商品数量
 * @property string $goods_type 商品类型
 * @property string $ecard_id 商品卡密id
 * @property Goods $goods
 * @property GoodsAttr $goodsAttr
 */
class OrderGoodsAttr extends Model
{
    public $id;
    public $goods_id;
    public $sign_id;
    public $stock;
    public $price;
    public $original_price;
    public $no;
    public $weight;
    public $pic_url;

    public $individual_share;
    public $share_type;
    public $member_price;
    public $integral_price;
    public $use_integral;
    public $discount;
    public $extra;
    public $goods_warehouse_id;
    public $name;
    public $cover_pic;
    public $detail;
    public $pic_list;
    public $number;
    public $goods_share_level;
    public $attr_setting_type;
    public $goods_type;
    public $ecard_id;

    protected $goods;
    protected $goodsAttr;

    public function rules()
    {
        return [
            [['goods_id', 'stock', 'weight', 'id', 'goods_warehouse_id', 'use_integral', 'number'], 'integer'],
            [['price', 'original_price', 'member_price', 'integral_price'], 'number', 'min' => 0],
            [['sign_id', 'no', 'pic_url', 'name', 'cover_pic'], 'string', 'max' => 255],
            [['extra', 'discount'], 'safe']
        ];
    }

    /**
     * @param Goods $goods
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;
        $this->goods_warehouse_id = $goods->goods_warehouse_id;
        $this->name = $goods->goodsWarehouse->name;
        $this->cover_pic = $goods->goodsWarehouse->cover_pic;
        $this->detail = $goods->detail;
        $this->pic_list = $goods->goodsWarehouse->pic_url;
        $this->goods_type = $goods->goodsWarehouse->type;
        $this->ecard_id = $goods->goodsWarehouse->ecard_id;
    }

    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param GoodsAttr $goodsAttr
     * @throws \Exception
     */
    public function setGoodsAttr($goodsAttr)
    {
        if (!$goodsAttr instanceof GoodsAttr) {
            throw new \Exception('参数$goodsAttr必须是app\models\GoodsAttr的一个实例');
        }
        $this->goodsAttr = $goodsAttr;
        $this->attributes = $goodsAttr->attributes;
        $this->original_price = $this->price;
        $this->discount = [];
        $this->extra = $this->getAttrExtra();
        $this->setShare();
        $this->stock = CommonEcard::getCommon()->getEcardStock($this->stock, $this->goods);
    }

    public function getGoodsAttr()
    {
        return $this->goodsAttr;
    }

    /**
     * @param $goodsAttrId
     * @throws \Exception
     */
    public function setGoodsAttrById($goodsAttrId)
    {
        /* @var GoodsAttr $goodsAttr */
        $goodsAttr = GoodsAttr::find()->with('share')->where(['id' => $goodsAttrId])->one();
        if (!$goodsAttr) {
            throw new \Exception('无法查询到规格信息。');
        }
        $this->setGoodsAttr($goodsAttr);
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        $this->goodsAttr->attributes = $this->attributes;
        return $this->goodsAttr->update($runValidation, $attributeNames);
    }

    public function getAttrExtra()
    {
        return [];
    }

    public function setShare()
    {
        $goodsAttr = $this->goodsAttr;
        if ($this->goods->attr_setting_type == 1) {
            // 详细设置
            $shareLevelList = GoodsShare::findAll([
                'goods_id' => $goodsAttr->goods_id,
                'goods_attr_id' => $goodsAttr->id,
                'is_delete' => 0
            ]);
        } else {
            // 普通设置
            $shareLevelList = GoodsShare::findAll([
                'goods_id' => $goodsAttr->goods_id,
                'goods_attr_id' => 0,
                'is_delete' => 0
            ]);
        }
        $this->individual_share = $this->goods->individual_share;
        $this->attr_setting_type = $this->goods->attr_setting_type;
        $this->share_type = $this->goods->share_type;
        foreach ($shareLevelList as $item) {
            $this->goods_share_level[] = [
                'share_commission_first' => $item->share_commission_first,
                'share_commission_second' => $item->share_commission_second,
                'share_commission_third' => $item->share_commission_third,
                'level' => $item->level,
            ];
        }
    }
}
