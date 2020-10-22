<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/15
 * Time: 15:06
 */

namespace app\forms\common\goods;

use app\forms\common\vip_card\CommonVip;
use app\models\Goods;

class CommonGoodsVipCard extends CommonVip
{
    private $goods;

    /**
     * @param $goods
     * @return $this
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * 获取小程序前端超级会员卡商品信息
     * @return array
     */
    public function getAppoint()
    {
        if ($this->plugin !== false && $this->permission !== false) {
            return $this->plugin->getAppoint($this->goods);
        }
        return [
            'discount' => null,
            'is_my_vip_card_goods' => null,
            'is_vip_card_user' => 0
        ];
    }

    /**
     * @param $orderId
     * @param $order
     * @return array[]
     */
    public function getOrderInfo($orderId, $order)
    {
        if ($this->plugin !== false && $this->permission !== false) {
            $res = $this->plugin->getOrderInfo($orderId, $order);
        }
        if (!isset($res['discount_list'])) {
            $res =  [
                'discount_list' => []
            ];
        }
        return $res;
    }
}
