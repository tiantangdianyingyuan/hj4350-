<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/25
 * Time: 11:03
 */

namespace app\forms\api\order\strategy;

use app\models\FullReduceActivity;

class FullReduceStrategy
{
    /**@var FullReduceStrategyAbstract $strategy**/
    private $strategy;

    /**
     * 初始时，传入具体的策略对象
     * @param $mode
     */
    public function __construct($mode)
    {
        $this->strategy = $mode;
    }

    /**
     * 执行优惠算法
     * @param FullReduceActivity $activity
     * @param $mchItem
     * @param $totalGoodsOriginalPrice
     * @param $totalGoodsPrice
     * @return mixed
     */
    public function get(FullReduceActivity $activity, $mchItem, $totalGoodsOriginalPrice, $totalGoodsPrice)
    {
        return $this->strategy->discount($activity, $mchItem, $totalGoodsOriginalPrice, $totalGoodsPrice);
    }
}
