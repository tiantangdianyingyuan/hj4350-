<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/6
 * Time: 9:56
 */

namespace app\plugins\advance\forms\common;

use app\models\Model;
use app\plugins\advance\models\AdvanceGoods;

class CommonForm extends Model
{
    /**
     * 判断商品所处时间段
     * @param $advanceGoods
     * @return int
     * 1:预售前
     * 2:预售中
     * 3:尾款中
     * 4:尾款结束
     */
    public static function timeSlot($advanceGoods)
    {
        $now = time();
        $start = strtotime($advanceGoods['start_prepayment_at']);
        $end = strtotime($advanceGoods['end_prepayment_at']);
        if ($advanceGoods['pay_limit'] != -1) {
            $payTime = $end + $advanceGoods['pay_limit']*24*60*60;
        } else {
            $payTime = -1;
        }
        if ($now < $start) {
            return 1;
        } elseif ($now >= $start && $now < $end) {
            return 2;
        } elseif ($payTime == -1 || $payTime >= $now ) {
            return 3;
        } else {
            return 4;
        }
    }
}