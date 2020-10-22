<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/25
 * Time: 10:53
 */

namespace app\forms\api\order\strategy;

/**
 * 实现循环满减
 * Class DiscountStrategy
 * @package app\forms\api\order\strategy
 */
class FullReduceLoopStrategy implements FullReduceStrategyAbstract
{
    /**
     * @param \app\models\FullReduceActivity $activity
     * @param $mchItem
     * @param $totalGoodsOriginalPrice
     * @param $totalGoodsPrice
     * @return mixed|void
     */
    public function discount($activity, $mchItem, $totalGoodsOriginalPrice, $totalGoodsPrice)
    {
        $rule = \Yii::$app->serializer->decode($activity->loop_discount_rule);
        if (!isset($rule['cut']) || !isset($rule['min_money'])) {
            return 0;
        }
        if ($totalGoodsOriginalPrice < $rule['min_money']) {
            return 0;
        }
        //循环满减
        return intval($totalGoodsPrice / $rule['min_money']) * $rule['cut'];
    }
}
