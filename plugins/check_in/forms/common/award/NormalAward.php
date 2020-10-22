<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/24
 * Time: 11:03
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common\award;


class NormalAward extends BaseAward
{
    public function check()
    {
        $common = $this->common;
        $sign = $common->getSignInByToday($this->user);
        if ($sign) {
            throw new \Exception('今天已签到');
        }
        $this->day = 1;
        return true;
    }

    public function otherSave()
    {
        $common = $this->common;
        $checkInUser = $common->getCheckInUser($this->user);
        $checkInUser->total += 1;

        $yesterday = $common->getSignInByYesterday($this->user);
        if ($yesterday) {
            $checkInUser->continue += 1;
        } else {
            $checkInUser->continue = 1;
        }
        if ($checkInUser->continue == 1) {
            $checkInUser->continue_start = mysql_timestamp();
        }
        if (!$checkInUser->continue_start) {
            $time = time() - ($checkInUser->continue - 1) * 86400;
            $checkInUser->continue_start = mysql_timestamp($time);
        }
        if (!$checkInUser->save()) {
            throw new \Exception($checkInUser);
        }
        return true;
    }
}
