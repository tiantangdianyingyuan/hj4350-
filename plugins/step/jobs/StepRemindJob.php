<?php

namespace app\plugins\step\jobs;

use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\StepNoticeTemplate;
use app\plugins\step\models\StepUser;
use yii\base\Component;
use yii\queue\JobInterface;

class StepRemindJob extends Component implements JobInterface
{
    public $model;

    public function execute($queue)
    {
        try {
            $this->checkRemindTimeOut();
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 提醒处理
     * @param $event
     */
    public function checkRemindTimeOut()
    {
        $cache = \Yii::$app->cache;
        $mall_id = $this->model->mall_id;
        $setting = CommonStep::getSetting($mall_id);

        $remind_at = $setting['remind_at'];
        if (date('H:i') < $remind_at) {
            return true;
        }

        $key = 'step_daily' . $mall_id;
        $stepDaily = $cache->get($key);
        if ($stepDaily && $stepDaily == date('Y-m-d')) {
            return true;
        }

        $stepUser = StepUser::find()->alias('s')->where([
                's.mall_id' => $mall_id,
                's.is_delete' => 0,
                's.is_remind' => 1,
            ])->innerJoinWith('user')->all();

        if (!$stepUser) {
            return false;
        }
        foreach ($stepUser as $item) {
            try {
                $tplMsg = new StepNoticeTemplate();
                $tplMsg->title = '步数兑换';
                $tplMsg->time = mysql_timestamp();
                $tplMsg->remark = '每日兑换提醒！';
                $tplMsg->user = $item->user;
                $tplMsg->page = 'plugins/step/index/index';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::error($exception->getMessage());
            }
        }

        $cache->set($key, date('Y-m-d'));
        $id = \Yii::$app->queue->delay(strtotime("$remind_at + 1 day") - time())->push(new StepRemindJob([
             'model' => $this->model,
        ]));
    }
}
