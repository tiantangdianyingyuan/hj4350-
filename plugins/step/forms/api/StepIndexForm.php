<?php

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\plugins\step\forms\common\CommonSport;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\CommonStepNewUser;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityInfo;
use app\plugins\step\models\StepActivityLog;
use app\plugins\step\models\StepBannerRelation;
use app\plugins\step\models\StepDaily;
use app\plugins\step\models\StepUser;


class StepIndexForm extends Model
{
    public $parent_id;
    public $encrypted_data;
    public $iv;
    public $code;

    public function rules()
    {
        return [
            [['code', 'encrypted_data', 'iv'], 'string'],
            [['parent_id'], 'integer']
        ];
    }

    public function index()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'user_data' => $this->userData(),
                    'activity_data' => $this->activityData(),
                    'ad_data' => CommonStep::getAd(1),
                    'banner_list' => $this->getBanner(),
                    'template_remind' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['step_notice']),
                    'template_activity' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['enroll_success_tpl']),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        };
    }

    private function userData()
    {
        $query = StepUser::find()->alias('s')->where([
            's.mall_id' => \Yii::$app->mall->id,
            's.user_id' => \Yii::$app->user->id,
            's.is_delete' => 0
        ])->leftJoin(['yq' => StepUser::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['is_delete' => 0],
            ['>', 'created_at', date('Y-m-d')],
        ])
        ], 'yq.parent_id = s.id')
        ->leftJoin(['da' => StepDaily::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['>', 'created_at', date('Y-m-d')],
            ])
        ], 'da.step_id = s.id');
        $list = $query->page($pagination)
            ->select("s.*,
            (CASE WHEN SUM(da.num) IS NULL THEN 0 ELSE SUM(da.num) END) as convert_num,
            (CASE WHEN SUM(yq.invite_ratio) IS NULL THEN 0 ELSE SUM(yq.invite_ratio) END) as old_ratio, 
            count(yq.id) as daily_invite_num")
            ->groupBy("s.id")
            ->asArray()
            ->one();

        if (!$list) {
            $newUser = new CommonStepNewUser();
            $newUser->parent_id = $this->parent_id;
            try {
                return $newUser->save($this)->userData();
            } catch (\Exception $e) {
                $list = [
                    'id' => null,
                    'ratio' => 0,
                    'old_ratio' => 0,
                    'convert_num' => 0
                ];
            }
        }
        $setting = CommonStep::getSetting();
        $inviteList = StepUser::find()->alias('i')->where([
            'mall_id' => \Yii::$app->mall->id,
            'parent_id' => $list['id'],
            'is_delete' => 0,
        ])->with('user.userInfo')->orderBy('created_at desc')->page($paginatin, 4)->asArray()->all();

        foreach ($inviteList as $k => $v) {
            $inviteList[$k]['avatar'] = $v['user']['userInfo']['avatar'];
            unset($v['user']);
        }

        $list['invite_list'] = $inviteList; //邀请列表
        $list['all_invite'] = $paginatin->total_count; //总邀请数
        $list['daily_ratio'] = floor($list['ratio'] - $list['old_ratio']) / 10; // 今天加成数
        $list['ratio'] = floor($list['ratio']) / 10;
        $list['daily_real_num'] = (new CommonSport())->getSportClass($this->attributes); //今日真实步数
        $list['daily_num'] = bcmul($list['daily_real_num'], (1 + $list['daily_ratio'] / 1000));
        //判断
        if($setting['convert_max']) {
            $list['daily_num'] = $list['daily_num'] > $setting['convert_max'] ? $setting['convert_max'] : $list['daily_num'];
        }
        $list['daily_num'] = $list['daily_num'] > $list['convert_num'] ? bcsub($list['daily_num'], $list['convert_num'], 0) : 0; //今日可兑换步数
        ///？可兑换币数
        $list['daily_currency'] = CommonStep::getSetting()['convert_ratio'] > 0 ?
            floor($list['daily_num'] / CommonStep::getSetting()['convert_ratio'] * 100) / 100 : 0;
        return $list;
    }

    private function activityData()
    {
        if ($step = CommonStep::getUser()) {
            $step_id = $step->id;
        } else {
            $step_id = 0;
        }
        $query = StepActivity::find()->alias('l')->where([
            'AND',
            ['l.mall_id' => \Yii::$app->mall->id],
            ['l.is_delete' => 0],
            ['l.type' => 0],
            ['l.status' => 1]
        ])->andWhere([
            'OR',
            ['>', 'l.begin_at', date('Y-m-d')],
            [
                'AND',
                ['<=', 'l.begin_at', date('Y-m-d')],
                ['>=', 'l.end_at', date('Y-m-d')],
                ['is not', 'log.id', Null]
            ]
        ])->leftJoin(['log' => StepActivityLog::tableName()], "log.`activity_id` = l.`id` AND log.`mall_id` = l.`mall_id` AND log.`step_id` = $step_id");

        $info = $query
            ->orderBy(['log.status' => SORT_DESC, 'l.begin_at' => SORT_ASC, 'l.end_at' => SORT_ASC, 'l.id' => SORT_DESC])
            ->select(['l.*', "log.id as g_id", "log.status as g_status"])
            ->asArray()
            ->one();

        if (!empty($info)) {
            $query = StepActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'activity_id' => $info['id'],
            ]);
            $list = $query->select("*, SUM(step_currency) AS act_currency")->asArray()->one();
            $info['log_status'] = $info['g_id'] ? $info['g_status'] : null;

            if(!is_null($info['log_status'])) {
                $total_num = StepActivityInfo::find()->select(["IF(SUM(num), SUM(num), 0)"])->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'activity_log_id' => $info['g_id']
                ])->column();
                $info['user_total_num'] = current($total_num);
            }
            $info['people_num'] = $query->count();//参与人数
            $info['total_currency'] =  floor(($info['currency'] + $list['act_currency']) * 100) / 100; //奖金池
            $info['people_reach_num'] = $query->andWhere(['status' => 1])->count();//达标数
            $info['now_time_status'] = $info['begin_at'] <= date('Y-m-d') && $info['end_at'] >= date('Y-m-d');
        }
        return $info;
    }

    private function getBanner()
    {
        $list = StepBannerRelation::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])->with('banner')
        ->asArray()
        ->all();

        $list = array_map(function ($item) {
            return $item['banner'];
        }, $list);

        return $list;
    }

    public function setting()
    {
        try {
            $setting = CommonStep::getSetting();
            $setting = \yii\helpers\ArrayHelper::toArray($setting);
            $keyword_arr = explode("\r\n", trim($setting['share_title']));

            $data = array_merge($setting, [
                'share_title' => $keyword_arr[array_rand($keyword_arr)],
                'template_activity' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['enroll_success_tpl'])
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        };
    }
}
