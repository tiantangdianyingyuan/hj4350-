<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\region\forms\mall;

use app\plugins\region\models\RegionCashLog;
use app\plugins\region\models\RegionUserInfo;

class CommonForm
{
    //分红流水记录
    public static function cashLog($captain_id, $price, $type, $desc)
    {
        $model = new RegionCashLog();
        $model->mall_id = \Yii::$app->mall->id;
        $model->user_id = $captain_id;
        $model->type = $type;//收入分红流水,1收入，2支出
        $model->price = $price;
        $model->desc = $desc;
        return $model->save();
    }

    // 分红提现记录
    public static function bonusCash($user_id, $price, $type, $desc = '分红提现')
    {
        //增加流水
        if (!self::cashLog($user_id, $price, $type, $desc)) {
            throw new \Exception('提现完成-增加流水');
        }

        //减少总分红
        if ($type == 2) {
            $price = -$price;
        }
        if (!RegionUserInfo::updateAllCounters(['total_bonus' => $price], ['user_id' => $user_id])) {
            throw new \Exception('提现完成-调整总分红');
        }
    }
}