<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/23
 * Time: 15:56
 */

namespace app\forms\common\share;


use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use yii\db\Query;

/**
 * @property Mall $mall
 */
class CommonShareTeam extends Model
{
    public $mall;
    public $userInfo;

    public function info($userId, $status = 1)
    {
        if (!$this->userInfo) {
            /* @var UserInfo $userInfo */
            $userInfo = UserInfo::find()->with(['thirdChildren'])->where(['user_id' => $userId])->one();
            $this->userInfo = $userInfo;
        }
        $userInfo = $this->userInfo;
        $userList = [];
        switch ($status) {
            case 1:
                if (is_array($userInfo->firstChildren)) {
                    $userList = array_column($userInfo->firstChildren, 'user_id');
                }
                break;
            case 2:
                if (is_array($userInfo->secondChildren)) {
                    $userList = array_column($userInfo->secondChildren, 'user_id');
                }
                break;
            case 3:
                if (is_array($userInfo->thirdChildren)) {
                    $userList = array_column($userInfo->thirdChildren, 'user_id');
                }
                break;
            default:
                $userList = [$userId];
        }
        return $userList;
    }
}
