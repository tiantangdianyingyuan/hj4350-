<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/21
 * Time: 13:47
 */

namespace app\plugins\flash_sale\forms\common;

use app\models\Order;
use app\models\OrderDetail;
use app\plugins\flash_sale\models\FlashSaleActivity;
use app\plugins\flash_sale\models\FlashSaleGoods;
use Yii;
use yii\db\ActiveRecord;

class CommonStatics
{
    /**
     * 统计活动时间内，订单实付金额 支付订单数 参与人数
     * @param $activityId
     * @param $start
     * @param string $end
     * @return array|ActiveRecord|null
     */
    public static function getStatics($activityId, $start = '', $end = '')
    {
        return Order::find()->alias('o')
            ->where(
                [
                    'o.is_recycle' => 0,
                    'o.is_pay' => 1,
                    'o.mall_id' => Yii::$app->mall->id,
                    'o.is_delete' => 0,
                    'o.mch_id' => 0,
                ]
            )
            ->leftJoin(['od' => OrderDetail::tableName()], 'o.id = od.order_id')
            ->leftJoin(['fg' => FlashSaleGoods::tableName()], 'fg.goods_id = od.goods_id')
            ->leftJoin(['fa' => FlashSaleActivity::tableName()], 'fa.id = fg.activity_id')
            ->where(['od.sign' => 'flash_sale', 'fa.is_delete' => 0, 'fa.id' => $activityId, 'o.is_pay' => 1])
            ->andWhere(['not', ['o.cancel_status' => 1]])
            ->keyword($start, ['<=', 'o.created_at', $start])
            ->keyword($end, ['<=', 'o.created_at', $end])
            ->select(
                "COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(DISTINCT `o`.`id`) AS `order_num`,IFNULL(SUM(`od`.`total_price`),0) AS `total_pay_price`"
            )
            ->orderBy('o.created_at')
            ->asArray()
            ->one();
    }
}
