<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019年12月14日 10:53:47
 * Time: 18:39
 */

namespace app\plugins\stock\forms\common;

use app\models\Model;
use app\models\UserInfo;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockSetting;

class CommonForm extends Model
{
    public static $members = [];

    //下级
    public static function allMembers($user_id)
    {
        self::$members[] = $user_id;
        $users = UserInfo::find()->alias('u')->where(['u.parent_id' => $user_id,])->with(['identity'])->all();
        $sum = count($users);
        foreach ($users as $user) {
            if (!in_array($user->user_id, self::$members)) {
                $sum += self::allMembers($user->user_id);
            }
        }
        return $sum;
    }


    /**
     * 获取股东信息及分红利率
     * @param $user_id
     * @return StockUser|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    public static function captain($user_id)
    {
        $captain = StockUser::find()
            ->where(['user_id' => $user_id, 'is_delete' => 0, 'status' => CommonStock::STATUS_BECOME])
            ->with(['level'])
            ->asArray()
            ->one();
        if (!$captain) {
            throw new \Exception('股东不存在');
        }
        if (!empty($captain['level'])) {
            $captain['bonus_rate'] = $captain['level']['rate'];
        } else {
            $bonusRate = StockSetting::get(\Yii::$app->mall->id, 'bonus_rate', 0);
            $captain['bonus_rate'] = $bonusRate;
        }
        return $captain;
    }
}
