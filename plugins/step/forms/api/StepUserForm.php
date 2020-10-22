<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepDaily;
use app\plugins\step\models\StepLog;
use app\plugins\step\models\StepUser;

class StepUserForm extends Model
{
    public $page;
    public $type;
    public $limit;
    public $is_remind;
    public $status;

    public function rules()
    {
        return [
            [['page', 'type', 'is_remind', 'status'], 'integer'],
            [['status'], 'in', 'range' => [1,2]],
            [['limit'], 'default', 'value' => 10],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = CommonStep::getUser();

            $list = StepLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'step_id' => $stepUser->id,
            ])->keyword($this->type == 1, ['type' => 1])
                ->keyword($this->type == 2, ['type' => 2])
                ->orderBy('id desc')
                ->asArray()
                ->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'stepUser' => $stepUser,
                    'list' => $list
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function remind()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = CommonStep::getUser();
            $stepUser->is_remind = $this->is_remind;
            if ($stepUser->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            } else {
                return $this->getErrorResponse($stepUser);
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
    
    public function inviteList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = CommonStep::getUser();

            $query = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'parent_id' => $stepUser->id,
                'is_delete' => 0,
            ])->with('user.userInfo');

            $invite_list = $query->orderBy('created_at desc')
                    ->page($pagination, 15)
                    ->asArray()
                    ->all();

            $now_count = $query->andWhere(['>','created_at',date("Y-m-d")])->count();

            foreach ($invite_list as &$v) {
                $v['nickname'] = $v['user']['nickname'];
                $v['avatar'] = $v['user']['userInfo']['avatar'];
                unset($v['user']);
            }
            unset($v);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $invite_list,
                    'count' => $pagination->total_count,
                    'now_count' => $now_count,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function ranking()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $stepUser = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->with('user.userInfo')->asArray()->one();
            if ($this->status == 1) {
                $query = StepUser::find()->where([
                    'AND',
                    ['>', 'step_currency', 0],
                    ['mall_id' => \Yii::$app->mall->id],
                    ['is_delete' => 0],
                ])->andWhere([
                    'OR',
                    ['user_id' => $stepUser['user_id']],
                    ['parent_id' => $stepUser['id']],
                    ['id' => $stepUser['parent_id']],
                ]);
            }
            if ($this->status == 2) {
                //全国限制
                $setting = CommonStep::getSetting();

                $offset = $this->limit * ($this->page - 1);
                if ($setting['ranking_num'] && $offset + $this->limit > $setting['ranking_num']) {
                    $this->limit = $setting['ranking_num'] > $offset ? $setting['ranking_num'] - $offset : 0;
                };

                $query = StepUser::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0
                ]);
            }

            $list = $query->andWhere(['>', 'step_currency', 0])
                ->with('user.userInfo')
                ->page($pagination, $this->limit)
                ->orderBy('step_currency desc')
                ->asArray()
                ->all();

            foreach ($list as &$v) {
                $v['nickname'] = $v['user']['nickname'];
                $v['avatar'] = $v['user']['userInfo']['avatar'];
                unset($v['user']);
            }
            unset($v);

            $stepUser['raking'] = $query->andWhere(['>=','step_currency',$stepUser['step_currency']])->count();
            $stepUser['nickname'] = $stepUser['user']['nickname'];
            $stepUser['avatar'] = $stepUser['user']['userInfo']['avatar'];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'user' => $stepUser,
                    'ad_data' => CommonStep::getAd(3),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function stepConvert()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->status == 1) {
                $begin_at = date('Y-m-d');
                $end_at = date("Y-m-d", strtotime("+1 day"));
            }
            if ($this->status == 2) {
                $begin_at = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d")-date("w")+1, date("Y")));
                $end_at = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d")-date("w")+7, date("Y")));
            }

            $query = StepDaily::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['>=', 'created_at', $begin_at],
                ['<=', 'created_at', $end_at],
            ]);


            $list = $query->select('*,SUM(num) AS total_num')
                ->groupBy('step_id')
                ->orderBy('total_num desc')
                ->having(['>', 'total_num', 0])
                ->with('step.user.userInfo')
                ->page($pagination)
                ->asArray()
                ->all();

            $stepUser = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->with('user.userInfo')->asArray()->one();
            $query1 = clone $query;
            $now = $query->select('*, SUM(num) AS total_num')
                ->andWhere(['step_id' => $stepUser['id']])
                ->with('step.user.userInfo')
                ->asArray()
                ->one();

            $stepUser['total_num'] = $now['total_num'];
            $stepUser['raking'] = $query1->select('*, SUM(num) AS total_num')->having(['>=', 'total_num', $now['total_num']])->count();
            $stepUser['nickname'] = $stepUser['user']['nickname'];
            $stepUser['avatar'] = $stepUser['user']['userInfo']['avatar'];

            foreach ($list as &$v) {
                $v['nickname'] = $v['step']['user']['nickname'];
                $v['avatar'] = $v['step']['user']['userInfo']['avatar'];
            }
            unset($v);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'user' => $stepUser,
                    'ad_data' => CommonStep::getAd(3),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
