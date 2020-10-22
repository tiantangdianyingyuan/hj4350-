<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/16
 * Time: 11:00
 */

namespace app\forms\common\full_reduce;

use app\models\FullReduceActivity;
use app\models\Goods;
use app\models\Model;

/**
 * Class CommonActivity
 * @package app\forms\common\form
 */
class CommonActivity extends Model
{
    /**
     * 获取活动规则
     * @return array
     */
    public static function getActivityMarket()
    {
        /**@var FullReduceActivity|null $activity**/
        $activity = FullReduceActivity::getNowActivity();
        if (!$activity) {
            return [];
        }
        if ($activity->rule_type == 1) {
            $discountInfo = \Yii::$app->serializer->decode($activity->discount_rule);
        } else {
            $discountInfo =  \Yii::$app->serializer->decode($activity->loop_discount_rule);
        }
        return [
            'rule_type' => $activity->rule_type,
            'rule' => $discountInfo,
            'content' => $activity->content,
            'time' => $activity->end_at
        ];
    }

    private static $rule;

    /**
     * 获取商品活动规则
     * @param Goods $goods
     * @return array
     */
    public static function getGoodsMarket(Goods $goods)
    {
        if (!$goods) {
            return [];
        }
        /**@var FullReduceActivity|null $activity**/
        $activity = FullReduceActivity::getNowActivity();
        if (!$activity) {
            return [];
        }
        if (self::$rule) {
            $rule = self::$rule;
        } else {
            $rule = self::getRules();
            self::$rule = $rule;
        }

        if (!in_array($goods->sign, $rule['rules'])) {
            return [];
        }

        if ($activity->appoint_type == 2 && $goods->mch_id != 0) {
            return [];
        }
        if ($activity->appoint_type == 3) {
            $appoint = (array)\Yii::$app->serializer->decode($activity->appoint_goods);
            if (!in_array($goods->goods_warehouse_id, $appoint)) {
                return [];
            }
        }
        if ($activity->appoint_type == 4) {
            $noappoint = (array)\Yii::$app->serializer->decode($activity->noappoint_goods);
            if (in_array($goods->goods_warehouse_id, $noappoint)) {
                return [];
            }
        }
        if ($activity->rule_type == 1) {
            $discountInfo = \Yii::$app->serializer->decode($activity->discount_rule);
        } else {
            $discountInfo =  \Yii::$app->serializer->decode($activity->loop_discount_rule);
        }
        return [
            'rule_type' => $activity->rule_type,
            'rule' => $discountInfo
        ];
    }

    /**
     * @param string[] $plugins
     * @return mixed
     */
    public static function getRules($plugins = ['gift', 'booking', 'composition', 'exchange', 'flash_sale', 'gift', 'miaosha', 'pintuan', 'advance'])
    {
        $list['rules'] = ['', 'mch', 'ecard'];
        foreach ($plugins as $item) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item);
                if (!method_exists($plugin, 'getEnableFullReduce')) {
                    continue;
                }
                if ($plugin->getEnableFullReduce()) {
                    $list['rules'][] = $plugin->getName();
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $list;
    }
}
