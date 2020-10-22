<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/5
 * Time: 17:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\goods;


use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\video\Video;
use app\forms\permission\role\AdminRole;
use app\forms\permission\role\SuperAdminRole;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class BaseApiGoods
 * @package app\forms\api\goods
 * @property Goods $goods
 * @property Mall $mall
 */
class ApiGoods extends Model
{
    private static $instance;
    public $goods;
    public $mall;
    public $isSales = 1;

    public $hasMember = false;
    public $tempGoodsDetail;
    public $deleteAttr = false;

    public static function getCommon($mall = null)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        self::$instance->mall = $mall;
        return self::$instance;
    }

    private function defaultData()
    {
        return [
            'id' => '商品id',
            'goods_warehouse_id' => '商品库id',
            'name' => '商品名称',
            'cover_pic' => '商品缩略图',
            'original_price' => '商品原价',
            'unit' => '商品单位',
            'page_url' => '商品跳转路径',
            'is_negotiable' => '是否价格面议',
            'is_level' => '是否享受会员',
            'level_price' => '会员价',
            'price' => '售价',
            'price_content' => '售价文字版',
            'is_sales' => '是否显示销量',
            'sales' => '销量',
        ];
    }

    public function getDetail()
    {
        $isNegotiable = $this->getNegotiable();
        $isSales = $this->getIsSales();
        try {
            $attrGroups = \Yii::$app->serializer->decode($this->goods->attr_groups);
        } catch (\Exception $exception) {
            $attrGroups = [];
        }

        $minPrice = $this->goods->attr[0]->price ?? 0;
        foreach ($this->goods->attr as $key => $value) {
            $minPrice = min($minPrice, $value->price);
        }

        $goodsStock = array_sum(array_column($this->goods->attr, 'stock')) ?? 0;
        $goodsStock = CommonEcard::getCommon()->getEcardStock($goodsStock, $this->goods);
        $data = [
            'id' => $this->goods->id,
            'goods_warehouse_id' => $this->goods->goods_warehouse_id,
            'mch_id' => $this->goods->mch_id,
            'sign' => $this->goods->sign,
            'name' => $this->goods->name,
            'subtitle' => $this->goods->subtitle,
            'cover_pic' => $this->goods->coverPic,
            'video_url' => Video::getUrl(trim($this->goods->videoUrl)),
            'original_price' => $this->goods->originalPrice,
            'unit' => $this->goods->unit,
            'page_url' => $this->goods->pageUrl,
            'is_negotiable' => $isNegotiable,
            'is_level' => $this->goods->is_level,
            'level_price' => $this->getGoodsMember(),
            'price' => $minPrice,
            'price_content' => $this->getPriceContent($isNegotiable, $minPrice),
            'is_sales' => $isSales,
            'sales' => $this->getSales($isSales, $this->goods->unit),
            'attr_groups' => $attrGroups,
            'attr' => $this->setAttr($this->goods->attr),
            'goods_stock' => $goodsStock,
            'goods_num' => $goodsStock,
            'type' => $this->goods->goodsWarehouse->type,
        ];
        $this->hasMember && $data['level_show'] = $this->getGoodsDetail()['level_show'];
        // 插件
        try {
            if ($this->goods->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->goods->sign);
                $config = $plugin->getOrderConfig();
                if ($config['is_member_price'] == 0) {
                    $data['is_level'] = 0;
                }
            }
        }catch (\Exception $exception) {
        }
        $data = array_merge($data, $this->getPlugin());
        if ($this->deleteAttr) {
            unset($data['attr']);
            unset($data['attr_groups']);
        }
        return $data;
    }

    /**
     * @return int
     * 获取是否价格面议
     */
    protected function getNegotiable()
    {
        $data = 0;
        if ($this->goods->sign == '') {
            $mallGoods = $this->goods->mallGoods;
            $data = $mallGoods->is_negotiable;
        }
        return $data;
    }

    /**
     * @return string
     * 获取会员价
     */
    protected function getGoodsMember()
    {
        return CommonGoodsMember::getCommon()->getGoodsMemberPrice($this->goods);
    }

    /**
     * @param int $isNegotiable
     * @param string $minPrice
     * @return string
     * 获取售价文字版
     */
    protected function getPriceContent($isNegotiable, $minPrice)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($minPrice > 0) {
            $priceContent = '￥' . $minPrice;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }

    /**
     * @return int|mixed
     * 获取是否显示销量
     */
    protected function getIsSales()
    {
        try {
            $setting = \Yii::$app->mall->getMallSetting(['is_show_sales_num']);
            $isSales = intval($setting['is_show_sales_num']);
        } catch (\Exception $exception) {
            $isSales = 1;
        }
        return $isSales;
    }

    /**
     * @param int $isSales
     * @param string $unit
     * @return string
     * 获取销量
     */
    protected function getSales($isSales, $unit = '件')
    {
        $sales = '';
        if ($this->isSales == 1 && $isSales == 1) {
            $sales = $this->goods->virtual_sales + $this->goods->sales;
            $length = strlen($sales);

            if ($length > 8) { //亿单位
                $sales = substr_replace(substr($sales, 0, -7), '.', -1, 0) . "亿";
            } elseif ($length > 4) { //万单位
                $sales = substr_replace(substr($sales, 0, -3), '.', -1, 0) . "w";
            }
            $sales = sprintf("已售%s%s", $sales, $unit);
        }
        return $sales;
    }

    /**
     * @return array
     * 获取插件中额外的信息
     */
    protected function getPlugin()
    {
        $list = [];
        try {
            $pluginList = \Yii::$app->mall->role->getPluginList();
            foreach ($pluginList as $plugin) {
                $list = array_merge($list, $plugin->getGoodsExtra($this->goods));
            }
        } catch (\Exception $exception) {
        }
        return $list;
    }

    private function getGoodsDetail()
    {
        if ($this->tempGoodsDetail) {
            return $this->tempGoodsDetail;
        }
        $form = new CommonGoodsDetail();
        $form->user = \Yii::$app->user->identity;
        $form->mall = \Yii::$app->mall;
        $form->goods = $this->goods;
        $this->tempGoodsDetail = $form->getAll(['attr']);
        return $this->tempGoodsDetail;
    }

    /**
     * 处理规格数据
     * @param null $attr
     * @return array
     * @throws \Exception
     */
    public function setAttr($attr = null)
    {
        if (!$this->goods) {
            throw new \Exception('请先设置商品对象');
        }
        //规格价
        if ($this->hasMember) {
            return $this->getGoodsDetail()['attr'];
        }

        if (!$attr) {
            $attr = $this->goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = $this->goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = $attrList[$item['sign_id']];
            $newItem['stock'] = CommonEcard::getCommon()->getEcardStock($item['stock'], $this->goods);
            $newAttr[] = $newItem;
        }
        return $newAttr;
    }
}
