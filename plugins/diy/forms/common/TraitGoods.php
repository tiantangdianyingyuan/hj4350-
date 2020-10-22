<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-08-12
 * Time: 15:32
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\common;

use app\forms\api\goods\ApiGoods;
use app\models\Goods;

trait TraitGoods
{
    /**
     * @param Goods[] $list
     * @return array
     * 获取商品列表
     */
    public function getGoodsList($list)
    {
        $newList = [];
        foreach ($list as $index => $goods) {
            if (!$this->goodsValidate($goods)) {
                continue;
            }
            $newList[] = $this->getGoodsDetail($goods);
        }
        return $newList;
    }

    /**
     * @param Goods $goods
     * @return mixed
     * 处理商品详情数据
     */
    public function getGoodsDetail($goods)
    {
        $apiGoods = ApiGoods::getCommon();
        $apiGoods->tempGoodsDetail = null;
        $apiGoods->goods = $goods;
        $apiGoods->hasMember = true;
        $apiGoods->deleteAttr = true;
        $arr = $apiGoods->getDetail();
        return $this->extraGoods($arr, $goods);
    }

    /**
     * @param array $arr
     * @param Goods $goods
     * @return array
     * 处理插件的商品详情数据
     */
    public function extraGoods($arr, $goods)
    {
        return $arr;
    }

    /**
     * @param Goods $goods
     * @return bool
     * 验证商品详情是否需要处理
     */
    public function goodsValidate($goods)
    {
        return true;
    }
}
