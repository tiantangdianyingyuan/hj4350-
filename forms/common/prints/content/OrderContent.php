<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 16:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\content;

/**
 * Class Order
 * @package app\forms\common\prints\content
 * @property GoodsContent[] $goods_list
 */
class OrderContent extends BaseContent
{
    public $mall_name;
    public $order_type;
    public $pay_type;
    public $send_type_text;
    public $order_no;
    public $created_at;

    public $is_attr;
    public $goods_list;

    public $total_goods_original_price;
    public $express_price;
    public $coupon_discount_price;
    public $use_integral_num;
    public $integral_deduction_price;
    public $member_discount_price;
    public $back_price;
    public $total_pay_price;

    public $send_type;
    public $name;
    public $mobile;
    public $address;
    public $store_name;
    public $store_mobile;
    public $store_address;
    public $remark;
    public $order_form = [];
    public $plugin_data = [];

    public $new_goods_list = [];
}
