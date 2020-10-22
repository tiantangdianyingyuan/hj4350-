<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/8
 * Time: 18:39
 */

namespace app\plugins\bonus\forms\common;

use app\models\Model;
use app\models\Share;
use app\models\UserInfo;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusSetting;

class CommonForm extends Model
{
    public static $exists = [];
    public static $members = [];
    public static $shares = [];
    public static $captains = [];

    /**查找最近的是队长的上级分销商**/
    public static function findFirstCaptain($user_id)
    {
        self::$exists[] = $user_id;
        if ($user_id == 0) {
            return 0;
        }
        $userInfo = UserInfo::find()->alias('u')->where(['u.user_id' => $user_id])->one();
        $captain = BonusCaptain::find()->where(['mall_id' => \Yii::$app->mall->id, 'status' => CommonCaptain::STATUS_BECOME, 'is_delete' => 0, 'user_id' => $userInfo->parent_id])->one();
        if (empty($captain) && !in_array($userInfo->parent_id, self::$exists)) {
            return self::findFirstCaptain($userInfo->parent_id);
        } elseif (in_array($userInfo->parent_id, self::$exists)) {
            //死循环了，强制跳出
            return 0;
        } else {
            return $userInfo->parent_id;
        }
    }

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

    public static function allShares($user_id)
    {
        self::$shares[] = $user_id;
        $users = UserInfo::find()->alias('u')
            ->innerJoin(['s' => Share::tableName()], 'u.user_id = s.user_id and s.status = 1 and s.is_delete = 0')
            ->where(['u.parent_id' => $user_id,])->with(['identity'])->all();
        $sum = count($users);
        foreach ($users as $user) {
            if ($user->identity->is_distributor == 1 ) {
                if (!in_array($user->user_id, self::$shares)) {
                    $sum += self::allShares($user->user_id);
                }
            }
        }
        return $sum;
    }

    /**
     * 统计所有的下级队长数量
     * @param $user_id
     * @return int|mixed
     */
    public static function allCaptains($user_id)
    {
        self::$captains[] = $user_id;
        $users = UserInfo::find()->alias('u')
            ->select('u.*,c.user_id as captain_id')
            ->innerJoin(['s' => Share::tableName()], 'u.user_id = s.user_id and s.status = 1 and s.is_delete = 0')
            ->innerJoin(['c' => BonusCaptain::tableName()], 'u.user_id = c.user_id and c.status = 1 and c.is_delete = 0')
            ->where(['u.parent_id' => $user_id,])->with(['identity'])->asArray()->all();
        $sum = 0;
        foreach ($users as $item) {
            if (!is_null($item['captain_id'])) {
                ++$sum;
            }
        }
        foreach ($users as $user) {
            if ($user['identity']['is_distributor'] == 1) {
                if ( !in_array($user['user_id'], self::$captains)) {
                    $sum += self::allCaptains($user['user_id']);
                }
            }
        }
        return $sum;
    }

    /**
     * 获取队长信息及分红利率
     * @param $user_id
     * @return BonusCaptain|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    public static function captain($user_id)
    {
        $captain = BonusCaptain::find()
            ->where(['user_id' => $user_id, 'is_delete' => 0, 'status' => CommonCaptain::STATUS_BECOME])
            ->with(['level'])
            ->asArray()
            ->one();
        if (!$captain) {
            throw new \Exception('队长不存在');
        }
        if (!empty($captain['level'])) {
            $captain['bonus_rate'] = $captain['level']['rate'];
        } else {
            $bonusRate = BonusSetting::get(\Yii::$app->mall->id, 'bonus_rate', 0);
            $captain['bonus_rate'] = $bonusRate;
        }
        return $captain;
    }
}
