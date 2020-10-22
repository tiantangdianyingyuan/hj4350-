<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;

/**
 * @property Mall $mall
 */
class ActivityLockingForm extends Model
{
    public $middleman_id;
    public $activity_id;
    public $is_locking;

    public function rules()
    {
        return [
            [['middleman_id', 'activity_id', 'is_locking'], 'required'],
            [['middleman_id', 'activity_id', 'is_locking'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = CommunityActivity::findOne($this->activity_id);
            if (empty($activity)) {
                throw new \Exception('活动不存在');
            }
            if (strtotime($activity->start_at) > time()) {
                throw new \Exception('活动未开始');
            }
            if (strtotime($activity->end_at) < time()) {
                throw new \Exception('活动已结束');
            }

            $model = CommunityActivityLocking::findOne(['activity_id' => $this->activity_id, 'middleman_id' => $this->middleman_id]);
            if (empty($model)) {
                $model = new CommunityActivityLocking();
                $model->middleman_id = $this->middleman_id;
                $model->activity_id = $this->activity_id;
            }
            $model->is_delete = $this->is_locking ? 0 : 1;
            if (!$model->save()) {
                throw new \Exception((new Model())->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'is_locking' => $model->is_delete ? -1 : 1
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
