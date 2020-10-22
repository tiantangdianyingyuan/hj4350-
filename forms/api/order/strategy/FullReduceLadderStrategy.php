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
 * 实现阶梯满减
 * Class FullReduceStrategy
 * @package app\forms\api\order\strategy
 */
class FullReduceLadderStrategy implements FullReduceStrategyAbstract
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
        $sub = 0;
        $rule = \Yii::$app->serializer->decode($activity->discount_rule);
        $rules = (array)$rule;
        if (empty($rule)) {
            return 0;
        }
        $rules = array_reverse($rules);
        foreach ($rules as $item) {
            if ($item['min_money'] <= $totalGoodsOriginalPrice) {
                if ($item['discount_type'] == 1) {
                    $sub = $item['cut'];
                } elseif ($item['discount_type'] == 2) {
                    $sub = (1 - $item['discount'] / 10) * $totalGoodsPrice;
                }
                break;
            }
        }
        return $sub;
    }
}
