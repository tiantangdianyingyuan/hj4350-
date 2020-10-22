<?php

namespace app\plugins\step\jobs;

use app\forms\common\template\tplmsg\ActivityRefundTemplate;
use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityLog;
use app\plugins\step\models\StepUser;
use yii\base\Component;
use yii\queue\JobInterface;

class StepActivityJob extends Component implements JobInterface
{
    public $model;

    public function execute($queue)
    {
        try {
            $this->checkPrizeTimeout();
        } catch (\Exception $e) {
            \Yii::warning($e->getMessage());
        }
    }

    /**
     * 处理中奖
     * @param $event
     */
    public function checkPrizeTimeout()
    {
        $mall_id = $this->model->mall_id;
        $activity = StepActivity::find()->where([
            'AND',
            ['mall_id' => $mall_id],
            ['is_delete' => 0],
            ['status' => 1],
            ['type' => 0]
        ])->andWhere(['<','end_at', date('Y-m-d')])->one();

        if ($activity) {
            $t = \Yii::$app->db->beginTransaction();
            //失败修改
            StepActivityLog::updateAll(['status' => 3,'raffled_at' => date('Y-m-d')], [
                'mall_id' => $mall_id,
                'activity_id' => $activity->id,
                'status' => 0
            ]);

            //失败参与活动人员
            $error_step_ids = StepActivityLog::find()->select('id, step_id')->where([
                'AND',
                ['mall_id' => $mall_id],
                ['status' => 0],
                ['activity_id' => $activity->id],
            ])->asArray()->all();
            $err_ids = array_column($error_step_ids, 'step_id');


            $query = StepActivityLog::find()->select('id, step_id')->where([
                'AND',
                ['mall_id' => $mall_id],
                ['status' => 1],
                ['activity_id' => $activity->id],
            ]);

            $count = $query->count();
            $success_step = $query->asArray()->all();
            $ok_ids = array_column($success_step, 'step_id');

            if (!$count) {
                $activity->type = 1;
                $activity->save();
                $t->commit();
                //失败通知
                $this->refundMsg($err_ids, $activity->title);
                return true;
            }

            //平方金额
            $currency = StepActivityLog::find()->select('SUM(step_currency) as total_num')->where([
                'mall_id' => $mall_id,
                'activity_id' => $activity->id,
            ])->asArray()->one();

            $currency_num = floor(($currency['total_num'] + $activity->currency) / $count * 100) / 100;

            //成功修改
            StepActivityLog::updateAll(['status' => 2,'raffled_at' => date('Y-m-d'), 'reward_currency' => $currency_num], [
                'mall_id' => $mall_id,
                'activity_id' => $activity->id,
                'status' => 1
            ]);

            //活动状态修改
            $activity->type = 1;
            if ($activity->save()) {
                array_walk($success_step, function ($item) use ($currency_num, $activity) {
                    (new CommonCurrencyModel())->setUser(CommonStep::getUser($item['step_id'], $this->model))->add($currency_num, $activity->title, '活动结算ID'. $activity->id);
                });
                $t->commit();
                //成功通知
                $this->successMsg($ok_ids, $activity->title);
                //失败通知
                $this->refundMsg($err_ids, $activity->title);
            } else {
                $t->rollBack();
            }
        } else {
            return false;
        }
    }

    public function successMsg($ids, $name)
    {
        $stepUser = StepUser::find()->alias('s')->where(['in', 's.id', $ids])->innerJoinWith('user')->all();
        if (!$stepUser) {
            return false;
        }
  
        foreach ($stepUser as $item) {
            try {
                $tplMsg = new ActivitySuccessTemplate();
                $tplMsg->activityName = '步数挑战';
                $tplMsg->name = $name;
                $tplMsg->remark = '恭喜您，奖励已发放！';
                $tplMsg->user = $item->user;
                $tplMsg->page = 'plugins/step/index/index';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }

    public function refundMsg($ids, $name)
    {
        $stepUser = StepUser::find()->alias('s')->where(['in', 's.id', $ids])->innerJoinWith('user')->all();
        if (!$stepUser) {
            return false;
        }

        foreach ($stepUser as $item) {
            try {
                $tplMsg = new ActivitySuccessTemplate();
                $tplMsg->activityName = '步数挑战';
                $tplMsg->name = $name;
                $tplMsg->remark = '很抱歉，本次未达标';
                $tplMsg->user = $item->user;
                $tplMsg->page = 'plugins/step/index/index';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }
}
