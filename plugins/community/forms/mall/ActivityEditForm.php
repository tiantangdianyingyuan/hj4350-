<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\community\jobs\ActivityJob;
use app\plugins\community\models\CommunityActivity;
use yii\db\Exception;

class ActivityEditForm extends Model
{
    public $id;
    public $keyword;
    public $status;
    public $title;
    public $start_at;
    public $end_at;
    public $is_area_limit;
    public $area_limit;
    public $full_price;
    public $condition;
    public $num;
    public $is_success;

    public function rules()
    {
        return [
            [['title', 'start_at', 'end_at',], 'required'],
            [['id', 'status', 'is_area_limit', 'condition', 'is_success'], 'integer'],
            [['keyword', 'full_price'], 'default', 'value' => ''],
            [['is_area_limit', 'num', 'is_success'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => CommunityActivity::ACTIVITY_UP],
            [['start_at', 'end_at', 'title', 'area_limit', 'full_price'], 'string'],
            [['area_limit',], 'default', 'value' => []],
            [['num'], 'integer', 'max' => 999999999],
            [['num'], 'integer', 'min' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '活动名称',
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
            'num' => '成团条件',
            'is_success' => '一键锁定成团'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->condition > 0 & $this->num <= 0) {
                throw new \Exception('成团条件数必须大于0');
            }
            $area_limit = '0,';
            $this->area_limit = json_decode($this->area_limit, true);
            if (!empty($this->area_limit) && is_array($this->area_limit)) {
                foreach ($this->area_limit as $item) {
                    $area_limit .= $item['id'] . ',';
                }
            }

            $ids = [0];
            if ($this->id) {
                array_push($ids, $this->id);
                $communityActivity = CommunityActivity::find()->where([
                    'id' => $this->id,
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id
                ])->one();

                if (empty($communityActivity)) {
                    throw new \Exception('活动不存在');
                }
                //活动时间判断
                if ($communityActivity->start_at != $this->start_at || $communityActivity->end_at != $this->end_at) {
                    throw new \Exception('当前活动不能更改时间');
                }
                if (strtotime($communityActivity->start_at) < time() && ($communityActivity->is_area_limit != $this->is_area_limit || $communityActivity->area_limit != $area_limit)) {
                    throw new \Exception('活动已开始，无法变更活动地区');
                }
//                }
                $this->status = $communityActivity->status;
            } else {
                $communityActivity = new CommunityActivity();
                $communityActivity->mall_id = \Yii::$app->mall->id;
            }
            //活动名称重复判断
            if (!empty(CommunityActivity::find()->andWhere(['and', ['mall_id' => \Yii::$app->mall->id], ['title' => $this->title], ['is_delete' => 0], ['not in', 'id', $ids]])->one())) {
                throw new Exception('活动名称重复');
            }
            if (strtotime($this->end_at) - time() <= 0) {
                throw new \Exception('活动结束时间必须晚于当前时间');
            }

            if ($this->is_area_limit == 1 && $this->area_limit == '[]') {
                throw new Exception('活动地区不能为空');
            }

            if ($this->full_price) {
                $i = 0;
                foreach (json_decode($this->full_price, true) as $item) {
                    $i++;
                    if ($i > 3) {
                        throw new Exception('优惠方式最多添加3条');
                    }
                    if ($item['full_price'] <= 0 || $item['reduce_price'] <= 0) {
                        throw new Exception('满减金额必须大于0');
                    }
                }
            }

            $communityActivity->status = $this->status;
            $communityActivity->title = $this->title;
            $communityActivity->start_at = $this->start_at;
            $communityActivity->end_at = $this->end_at;
            $communityActivity->is_area_limit = $this->is_area_limit;
            $communityActivity->area_limit = $area_limit;
            $communityActivity->full_price = $this->full_price;
            $communityActivity->condition = $this->condition;
            $communityActivity->num = $this->num;
            $res = $communityActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($communityActivity));
            }

            if (!$this->id) {
                //定时队列，到时判断活动成功，到时间后果60秒再执行
                \Yii::$app->queue->delay(strtotime($this->end_at) - time() + 60)
                    ->push(new ActivityJob(['mall' => \Yii::$app->mall, 'appVersion' => \Yii::$app->appPlatform, 'activity_id' => $communityActivity->id, 'user_id' => \Yii::$app->user->id]));
            }

            $this->id = $communityActivity->id;


            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'activity_id' => $communityActivity->id
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
        }
    }
}
