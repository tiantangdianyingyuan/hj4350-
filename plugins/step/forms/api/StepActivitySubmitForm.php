<?php

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\step\forms\common\CommonSport;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityInfo;
use app\plugins\wxapp\Plugin;

class StepActivitySubmitForm extends Model
{
    public $num;
    public $activity_id;

    public $encrypted_data;
    public $code;
    public $iv;

    public function rules()
    {
        return [
            [['activity_id', 'num'], 'required'],
            [['code', 'encrypted_data', 'iv'], 'string'],
            [['activity_id', 'num'], 'integer']
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            if ($this->num > (new CommonSport())->getSportClass($this->attributes)) {
                throw new \Exception('步数提交异常');
            };

            $stepUser = CommonStep::getUser();

            $activity = StepActivity::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['id' => $this->activity_id],
                ['is_delete' => 0],
                ['type' => 0],
                ['status' => 1],
                ['<=', 'begin_at', date("Y-m-d")],
            ])->with(['log' => function ($query) use ($stepUser) {
                $query->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'step_id' => $stepUser->id,
                ]);
            }])->one();

            if (!$activity) {
                throw new \Exception('活动已过期或不存在');
            }
            if (!$activity['log']) {
                throw new \Exception('尚未报名');
            }

            $model = StepActivityInfo::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'open_date' => date('Y-m-d'),
                'activity_log_id' => $activity['log']['id'],
            ])->one();

            if (!$model) {
                $model = new StepActivityInfo();
                $model->mall_id = \Yii::$app->mall->id;
                $model->activity_log_id = $activity['log']['id'];
                $model->open_date = date('Y-m-d');
            }
            $model->num = $this->num;

            if ($model->num >= $activity['step_num']) {
                $activity->log->status = 1;
                $activity->log->save();
            }
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $model->num,
                ];
            } else {
                throw new \Exception($this->getErrorMsg($model));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
