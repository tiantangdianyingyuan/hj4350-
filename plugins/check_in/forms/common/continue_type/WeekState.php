<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 16:01
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common\continue_type;


use app\plugins\check_in\jobs\ClearContinueJob;

class WeekState extends BaseState
{
    public function setJob()
    {
        $nextMonday = strtotime('next monday');
        $delay = $nextMonday - time();
        \Yii::$app->queue->delay($delay)->push(new ClearContinueJob([
            'mall' => $this->common->mall
        ]));
    }

    public function clearContinue()
    {
        $week = date('N');
        $count = 0;
        if ($week == 1) {
            $count = $this->common->clearContinue();
        }
        return $count;
    }
}
