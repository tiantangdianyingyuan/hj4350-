<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\tplmsg\ActivityRefundTemplate;
use app\models\Model;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityInfo;
use app\plugins\step\models\StepActivityLog;
use app\plugins\step\models\StepUser;

class ActivityForm extends Model
{
    public $id;
    public $status;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['keyword'], 'string']
        ];
    }

    //GET
    public function getList()
    {
        $query = StepActivity::find()->alias('a')->where([
            'a.mall_id' => \Yii::$app->mall->id,
            'a.is_delete' => 0,
        ])->leftJoin(['ag' => StepActivityLog::find()
            ->where([]),
        ], 'ag.activity_id = a.id');

        $query->keyword($this->keyword, ['like', 'title', $this->keyword]);
        $list = $query->select("a.*, count(ag.id) as people_num, sum(ag.step_currency) as currency_num")
            ->orderBy('a.id desc')
            ->groupBy('a.id')
            ->page($pagination)
            ->asArray()
            ->all();

        $list = array_map(function ($item) {
            if ($item['type'] == 1) {
                $item['activity_status'] = 'over'; //结束-已结束
                return $item;
            }
            if ($item['type'] == 2) {
                $item['activity_status'] = 'disbanded'; //结束-已解散
                return $item;
            }
            $begin_at = strtotime($item['begin_at']);
            $end_at = strtotime($item['end_at']);

            if ($item['end_at'] < date('Y-m-d', $_SERVER['REQUEST_TIME'])) {
                $item['activity_status'] = 'expired'; //过期
                return $item;
            }
            if ($item['type'] == 0 && $begin_at < $_SERVER['REQUEST_TIME'] && $item['status'] == 1) {
                $item['activity_status'] = 'start_y';//进行中-状态开启
                return $item;
            }
            if ($item['type'] == 0 && $begin_at < $_SERVER['REQUEST_TIME'] && $item['status'] == 0) {
                $item['activity_status'] = 'start_n';//进行中-状态关闭
                return $item;
            }
            if ($item['type'] == 0 && $begin_at > $_SERVER['REQUEST_TIME']) {
                $item['activity_status'] = 'no_start';//未开始
                return $item;
            }
        }, $list);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $list = StepActivity::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $this->id,
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepActivity::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        //['not',['type' => 0]]
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y - m - d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    public function editStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepActivity::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->status = $this->status;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }

    public function partakeList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = StepActivityLog::find()->alias('a')->where([
            'a.mall_id' => \Yii::$app->mall->id,
            'a.activity_id' => $this->id,
        ])->leftJoin(['ag' => StepActivityInfo::tableName()], 'ag.activity_log_id = a.id')
            ->with('step.user.userInfo');

        $list = $query->select("a.*, (CASE WHEN sum(ag.num) IS null THEN 0 ELSE sum(ag.num) END) total_num")
            ->page($pagination)
            ->groupBy('a.id')
            ->orderBy('status Asc, id DESC')
            ->asArray()
            ->all();
        foreach($list as $k => $v) {
            $list[$k]['nickname'] = $v['step']['user']['nickname'];
            $list[$k]['avatar'] = $v['step']['user']['userInfo']['avatar'];
            $list[$k]['platform'] = $v['step']['user']['userInfo']['platform'];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ],
        ];
    }

    //TODO
    public function disband()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = StepActivity::find()->where([
                'AND',
                ['id' => $this->id],
                ['mall_id' => \Yii::$app->mall->id],
                ['is_delete' => 0],
                ['type' => 0],
                //['<=', 'begin_at', date('Y-m-d')],
            ])->one();
            if (!$activity) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '活动不存在',
                ];
            }
            $query = StepActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'activity_id' => $activity->id,
            ]);
            $count = $query->count();

            if ($count == 0) {
                $activity->type = 2;
                $activity->save();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '解散成功'
                ];
            }
            $cli = clone $query;
            $list = $query->with('step')->all();
            $currency = $cli->select('avg(step_currency) as num')->asArray()->one();
            $currency_num = floor($currency['num'] * 100) / 100;

            $t = \Yii::$app->db->beginTransaction();

            array_walk($list, function ($model) use ($currency_num) {
                $model->status = 4;
                $model->save();
                if (!$model->step) {
                    return;
                }
                (new CommonCurrencyModel())->setUser($model->step)->add(floor($currency_num), '后台解散', '后台活动解散');
            });
            $activity->type = 2;
            $activity->save();
            $t->commit();
            $ids = array_column($list, 'step_id');
            $this->refundMsg($ids, $activity->title);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '解散成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function refundMsg($ids, $name)
    {
        $stepUser = StepUser::find()->alias('s')->where(['in', 's.id', $ids])->innerJoinWith('user')->all();
        if (!$stepUser) {
            return false;
        }

//        $tplMsg->method = 'activityRefundMsg';
//        $tplMsg->params = [
//            'activity_name' => '步数挑战',
//            'name' => $name,
//            'remark' => '很抱歉，活动被解散',
//            'page' => 'plugins / step / index / index',
//        ];

        /* @var StepUser $stepUser */
        foreach ($stepUser as $item) {
            try {
                $tplMsg = new ActivityRefundTemplate([
                    'page' => 'plugins/step/index/index',
                    'user' => $item->user,
                    'activityName' => '步数挑战',
                    'name' => $name,
                    'remark' => '很抱歉，活动呗解散'
                ]);
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }
}
