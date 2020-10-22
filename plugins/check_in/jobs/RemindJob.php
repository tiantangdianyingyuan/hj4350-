<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/29
 * Time: 13:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\jobs;


use app\models\Mall;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\models\CheckInConfig;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property CheckInConfig $config
 */
class RemindJob extends BaseObject implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        $this->mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($this->mall);
        $common = Common::getCommon($this->mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $config = $common->getConfig();
            if (!$config) {
                throw new \Exception('签到未开放');
            }
            if ($config->status == 0) {
                throw new \Exception('签到未开启');
            }
            if ($config->is_remind == 0) {
                throw new \Exception('签到未开启提醒功能');
            }
            $time = time();
            $configTime = strtotime($config->time);
            // 提醒时间没有到，重新添加定时任务
            if ($configTime - $time > 0) {
                return ;
            }

            $checkInUserAll = $common->getCheckInUserByRemind();

            foreach ($checkInUserAll as $checkInUser) {
                try {
                    $template = $common->getCommonTemplate($checkInUser->user);
                    $template->send();
                    $common->addCheckInUserRemind([
                        'user_id' => $checkInUser->user_id,
                        'mall_id' => $checkInUser->mall_id,
                        'is_delete' => 0,
                        'date' => date('Y-m-d H:i:s'),
                        'is_remind' => 1,
                    ]);
                } catch (\Exception $exception) {
                    continue;
                }
            }
            $common->addRemindJob();
            $t->commit();
        } catch (\Exception $exception) {
            $common->addRemindJob();
            $t->rollBack();
        }
    }
}
