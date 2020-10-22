<?php

namespace app\plugins\scan_code_pay\jobs;

use app\models\Model;
use app\plugins\scan_code_pay\models\ScanCodePayActivities;
use yii\base\Component;
use yii\queue\JobInterface;

class CreateActivityJob extends Component implements JobInterface
{
    public $activity_id;

    public function execute($queue)
    {
        /** @var ScanCodePayActivities $activity */
        $activity = ScanCodePayActivities::find()->where([
            'id' => $this->activity_id,
            'status' => 1,
        ]);
        if ($activity) {
           if (time() > strtotime($activity->end_time)) {
               $activity->status = 0;
               $res = $activity->save();
               if (!$res) {
                   \Yii::error((new Model())->getErrorMsg($activity));
               }
           }
        }
        \Yii::warning('当面付活动下架完成');
    }
}
