<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\forms\common\CommonSport;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\CommonStepNewUser;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityInfo;
use app\plugins\step\models\StepActivityLog;

class StepActivityForm extends Model
{
    public $page;
    public $id;
    public $activity_id;
    public $parent_id;

    public $encrypted_data;
    public $iv;
    public $code;

    public function rules()
    {
        return [
            [['page', 'id', 'activity_id', 'parent_id'], 'integer'],
            [['code', 'encrypted_data', 'iv'], 'string'],
        ];
    }
    //列表
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = CommonStep::getUser();
            if (!$stepUser) {
                $newUser = new CommonStepNewUser();
                $newUser->parent_id = $this->parent_id;
                return $newUser->save($this)->search();
            }

            $cuQuery = StepActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->andWhere('activity_id = l.id')->select('sum(step_currency)');

            $awQuery = StepActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'status' => 1,
            ])->andWhere('activity_id = l.id')->select('sum(1)');

            $peQuery = StepActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->andWhere('activity_id = l.id')->select('sum(1)');

            $query = StepActivity::find()->alias('l')->select(["l.*", "award_num" => $awQuery, "currency_num" => $cuQuery, "people_num" => $peQuery, "log.id as g_id", "log.status as g_status"])
                ->where([
                    'AND',
                    ['l.type' => 0],
                    ['l.is_delete' => 0],
                    ['l.status' => 1],
                    ['l.mall_id' => \Yii::$app->mall->id],
                ])->andWhere([
                    'OR',
                    ['>', 'begin_at', date('Y-m-d')],
                    [
                        'AND',
                        ['<=', 'begin_at', date('Y-m-d')],
                        ['>=', 'end_at', date('Y-m-d')],
                        ['is not', 'log.id', Null]
                    ]
                ])
                ->leftJoin(['log' => StepActivityLog::tableName()], "log.`activity_id` = l.`id` AND log.`mall_id` = l.`mall_id` AND log.`step_id` = $stepUser->id");
            $activityData = $query->orderBy('begin_at asc')->page($pagination, 3, $this->page)->asArray()->all();

            foreach ($activityData as &$item) {
                $item['award_num'] = $item['award_num'] ?:0;//达标人数
                $item['currency_num'] = floor(($item['currency_num'] + $item['currency']) * 100) / 100; // 奖金池
                $item['people_num'] = $item['people_num'] ?: 0;   // 参与人数
                $item['log_status'] = $item['g_id'] ? $item['g_status'] : null;
                $item['now_time_status'] = $item['begin_at'] <= date('Y-m-d') && $item['end_at'] >= date('Y-m-d');

                if(!is_null($item['log_status'])) {
                    $total_num = StepActivityInfo::find()->select(["IF(SUM(num), SUM(num), 0)"])->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'activity_log_id' => $item['g_id']
                    ])->column();
                    $item['user_total_num'] = current($total_num);
                }
            };
            unset($item);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'list' => [
                    'activity_data' => $activityData,
                    'daily_real_num' => (new CommonSport())->getSportClass($this->attributes),
                    'ad_data' => CommonStep::getAd(2),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = CommonStep::getUser();

            $query = StepActivityLog::find()->alias('s')->where([
                's.mall_id' => \Yii::$app->mall->id,
                's.step_id' => $stepUser->id,
            ])->joinWith(['activity a' => function ($query) {
                $query->where([
                    'AND',
                    ['a.mall_id' => \Yii::$app->mall->id],
                    ['a.is_delete' => 0],
                    ['not',['a.type' => 2]]
                ]);
            }]);

            $childQuery = StepActivityInfo::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->andWhere('activity_log_id = s.id')->select('sum(num)');


            $list = $query->select(['s.*',"user_num" => $childQuery])
                ->page($pagination)
                ->orderBy('begin_at desc')
                ->asArray()
                ->all();

            $info = $query->andWhere(['s.status' => 2])
                ->select("SUM(s.reward_currency) as total_currency,count(s.id) as bout, s.activity_id")
                ->groupBy('activity_id')
                ->asArray()
                ->one();

                $info['total_bout'] =  $pagination->total_count;
                $info['total_currency'] = $info['total_currency'] ??0;
                $info['bout'] = $info['bout'] ??0;
                $info['bout_ratio'] = $info['total_bout'] ? floor($info['bout'] / $info['total_bout'] * 100) : 0;

            foreach ($list as &$v) {
                $v['activity']['now_time_status'] = $v['activity']['begin_at'] <= date('Y-m-d') && $v['activity']['end_at'] >= date('Y-m-d');
            }
            unset($v);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'info' => $info
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = StepActivity::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->activity_id,
                'status' => 1,
                'type' => 0
            ])->asArray()->one();

            if (!$list) {
                throw new \Exception('活动已过期或不存在');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function join()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = StepActivity::findOne([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['>','begin_at',date('Y-m-d')],
                ['is_delete' => 0],
                ['id' => $this->activity_id],
                ['type' => 0],
                ['status' => 1]
            ]);

            if (!$activity) {
                throw new \Exception('活动已过期或不存在');
            }
            $stepUser = CommonStep::getUser();

            $log = StepActivityLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'step_id' => $stepUser->id,
                'activity_id' => $activity->id,
            ]);
            if ($log) {
                throw new \Exception('已参加');
            }

            //日志
            $t = \Yii::$app->db->beginTransaction();
            $model = new StepActivityLog();
            $model->mall_id = \Yii::$app->mall->id;
            $model->step_id = $stepUser->id;
            $model->activity_id = $activity->id;
            $model->step_currency = $activity->bail_currency;
            $model->status = 0;
            $model->raffled_at = '0000-00-00 00:00:00';

            if ($model->save()) {
                (new CommonCurrencyModel())->setUser()->sub(floor($activity->bail_currency), $activity->title, '活动报名ID'. $activity->id);
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'list' => [
                        'id' => $activity->id,
                        'bail_currency' => $activity->bail_currency,
                    ]
                ];
            } else {
                $t->rollBack();
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
