<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 18:19
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common;


use app\core\Pagination;
use app\helpers\PluginHelper;
use app\models\Formid;
use app\models\User;
use app\plugins\check_in\forms\common\award\BaseAward;
use app\plugins\check_in\forms\common\award\ContinueAward;
use app\plugins\check_in\forms\common\award\NormalAward;
use app\plugins\check_in\forms\common\award\TotalAward;
use app\plugins\check_in\forms\common\continue_type\MonthState;
use app\plugins\check_in\forms\common\continue_type\UnlimitedState;
use app\plugins\check_in\forms\common\continue_type\WeekState;
use app\plugins\check_in\forms\mall\CheckInAwardConfigForm;
use app\plugins\check_in\forms\Model;
use app\plugins\check_in\jobs\RemindJob;
use app\plugins\check_in\models\CheckInAwardConfig;
use app\plugins\check_in\models\CheckInConfig;
use app\plugins\check_in\models\CheckInCustomize;
use app\plugins\check_in\models\CheckInQueue;
use app\plugins\check_in\models\CheckInSign;
use app\plugins\check_in\models\CheckInUser;
use app\plugins\check_in\models\CheckInUserRemind;
use app\plugins\check_in\Plugin;

class Common extends Model
{
    public $config;
    public static $instance;

    /**
     * @param $mall
     * @return Common
     * @throws \Exception
     */
    public static function getCommon($mall)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $form = new Common();
        $form->mall = $mall;
        self::$instance = $form;
        return $form;
    }

    /**
     * @return CheckInConfig|null
     * 获取配置
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = CheckInConfig::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new CheckInConfig();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;
        return $config;
    }

    /**
     * @return CheckInAwardConfig[]
     * 获取所有的奖励配置
     */
    public function getAwardConfigAll()
    {
        $awardConfigAll = CheckInAwardConfig::findAll([
            'mall_id' => $this->mall->id, 'is_delete' => 0
        ]);
        return $awardConfigAll;
    }

    /**
     * @return CheckInAwardConfig|null
     * 获取普通签到奖励
     */
    public function getAwardConfigNormal()
    {
        $awardConfigNormal = CheckInAwardConfig::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1
        ]);
        return $awardConfigNormal;
    }

    /**
     * @param CheckInUser $checkInUser
     * @return array|\yii\db\ActiveRecord[]
     * 获取连续签到奖励
     */
    public function getAwardConfigContinue($checkInUser)
    {
        $userId = $checkInUser ? $checkInUser->user_id : 0;
        $continue = $checkInUser ? $checkInUser->continue : 0;
        $continueStart = $checkInUser ? $checkInUser->continue_start : '';
        $query = CheckInSign::find()->alias('us')->where([
            'us.user_id' => $userId, 'us.status' => 2
        ])->keyword($checkInUser, ['<=', 'us.day', $continue])
            ->keyword($checkInUser, ['>=', 'us.created_at', $continueStart])
            ->select('us.day, us.id');

        $awardConfigContinue = CheckInAwardConfig::find()->alias('a')->where([
            'a.mall_id' => $this->mall->id, 'a.is_delete' => 0, 'a.status' => 2
        ])->leftJoin(['us' => $query], 'us.day = a.day')->select(['a.*', 'us.id check'])
            ->orderBy(['us.day' => SORT_ASC, 'a.day' => SORT_ASC])
            ->limit(3)->asArray()->all();
        return $awardConfigContinue;
    }

    /**
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     * 获取累计签到奖励
     */
    public function getAwardConfigTotal($user)
    {
        $userId = $user ? $user->id : 0;
        $query = CheckInSign::find()->alias('us')
            ->where(['us.user_id' => $userId, 'status' => 3])
            ->select('us.day, us.id');

        $awardConfigTotal = CheckInAwardConfig::find()->alias('a')->where([
            'a.mall_id' => $this->mall->id, 'a.is_delete' => 0, 'a.status' => 3
        ])->leftJoin(['us' => $query], 'us.day = a.day')->select(['a.*', 'us.id check'])
            ->orderBy(['us.day' => SORT_ASC, 'a.day' => SORT_ASC])
            ->limit(1)->asArray()->all();
        return $awardConfigTotal;
    }

    /**
     * @param $user
     * @return CheckInUser|null
     * 获取用户签到信息
     */
    public function getCheckInUser($user)
    {
        if (!$user) {
            return null;
        }
        $checkInUser = CheckInUser::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$checkInUser) {
            $checkInUser = new CheckInUser();
            $checkInUser->mall_id = $this->mall->id;
            $checkInUser->user_id = $user->id;
            $checkInUser->total = 0;
            $checkInUser->continue = 0;
            $checkInUser->is_remind = 0;
            $checkInUser->is_delete = 0;
            $checkInUser->save();
        }
        return $checkInUser;
    }

    /**
     * @param CheckInConfig|null $config
     * @param $attribute
     * @return CheckInConfig|string
     * @throws \Exception
     * 编辑配置
     */
    public function addConfig($config, $attribute)
    {
        $config->is_delete = 0;
        $config->attributes = $attribute;
        if (!$config->save()) {
            throw new \Exception($this->getErrorMsg($config));
        }
        return $config;
    }

    /**
     * @param array $newList
     * @return CheckInAwardConfig|null
     * @throws \Exception
     * 编辑奖励
     */
    public function addAwardConfig($newList)
    {
        /* @var CheckInAwardConfig[] $awardConfigAll */
        $awardConfigAll = CheckInAwardConfig::findAll([
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ]);
        $awardConfigAll = array_map(function ($v) {
            $v->is_delete = 1;
            return $v;
        }, $awardConfigAll);
        $list = [];
        foreach ($newList as $item) {
            $attribute = new CheckInAwardConfigForm();
            $attribute->scenario = $item['type'];
            $attribute->attributes = $item;
            $attribute->search();
            $awardConfig = null;
            foreach ($awardConfigAll as $key => &$value) {
                if ($value->day == $attribute->day && $value->status == $attribute->status) {
                    $awardConfig = $value;
                    // 删除去除的对象
                    unset($awardConfigAll[$key]);
                    break;
                }
            }
            unset($value);
            if (!$awardConfig) {
                $awardConfig = new CheckInAwardConfig();
                $awardConfig->day = $attribute->day;
                $awardConfig->status = $attribute->status;
                $awardConfig->mall_id = $this->mall->id;
            }
            $awardConfig->is_delete = 0;
            $awardConfig->number = $attribute->number;
            $awardConfig->type = $attribute->type;
            $list[] = $awardConfig;
        }
        // 重排列数组
        $awardConfigAll = array_values($awardConfigAll);
        $list = array_merge($list, $awardConfigAll);
        /* @var CheckInAwardConfig[] $list */
        foreach ($list as $item) {
            if (!$item->save()) {
                throw new \Exception($this->getErrorMsg($item));
            }
        }
        return $list;
    }

    /**
     * @param $token
     * @param $data
     * @return bool
     * 添加队列处理日志
     */
    public function addQueueData($token, $data)
    {
        $form = new CheckInQueue();
        $form->token = $token;
        $form->data = $data;
        $form->save();
        return true;
    }

    /**
     * @param $token
     * @return CheckInQueue|null
     * 通过token获取队列处理结果
     */
    public function getQueueData($token)
    {
        $queueData = CheckInQueue::findOne(['token' => $token]);
        return $queueData;
    }

    /**
     * @param $user
     * @return array|\yii\db\ActiveRecord|null|CheckInSign
     * 获取今天签到的奖励信息
     */
    public function getSignInByToday($user)
    {
        if (!$user) {
            return null;
        }
        $start = date('Y-m-d', time());
        $end = date('Y-m-d H:i:s', (strtotime($start) + 86400 - 1));
        $signIn = CheckInSign::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $user->id, 'status' => 1])
            ->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])
            ->orderBy(['id' => SORT_DESC])->one();
        return $signIn;
    }


    /**
     * @param $user
     * @return array|\yii\db\ActiveRecord|null|CheckInSign
     * 获取昨天签到的奖励信息
     */
    public function getSignInByYesterday($user)
    {
        // 今天凌晨
        $start = date('Y-m-d', time());
        // 昨天凌晨
        $start = date('Y-m-d', strtotime($start) - 86400);
        // 昨天半夜
        $end = date('Y-m-d H:i:s', (strtotime($start) + 86400 - 1));
        $signIn = CheckInSign::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $user->id, 'status' => 1])
            ->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])
            ->orderBy(['id' => SORT_DESC])->one();
        return $signIn;
    }

    /**
     * @param integer $day
     * @param User $user
     * @param integer $status
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定天数指定用户的奖励信息
     */
    public function getSignInByDay($status, $day, $user)
    {
        $sign = CheckInSign::find()->where([
            'mall_id' => $this->mall->id, 'user_id' => $user->id, 'status' => $status, 'day' => $day, 'is_delete' => 0
        ])->one();
        return $sign;
    }

    /**
     * @param $status
     * @param $day
     * @return CheckInAwardConfig|null
     * 获取指定天数的奖励方案
     */
    public function getAwardByDay($status, $day)
    {
        $award = CheckInAwardConfig::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => $status, 'day' => $day
        ]);
        return $award;
    }

    /**
     * @param $token
     * @param $user
     * @return CheckInSign|null
     * 获取指定token的奖励信息
     */
    public function getSignInByToken($token, $user)
    {
        $sign = CheckInSign::findOne(['token' => $token, 'mall_id' => $this->mall->id, 'user_id' => $user->id]);
        return $sign;
    }

    /**
     * @param $month
     * @param $year
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     * 获取指定月用户签到信息
     */
    public function getCheckInDayByMonth($month, $user, $year = null)
    {
        if (!$year) {
            $year = date('Y', time());
        }
        $sign = CheckInSign::find()
            ->where(['mall_id' => $this->mall->id, 'user_id' => $user->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['MONTH(`created_at`)' => $month, 'YEAR(created_at)' => $year])->all();
        return $sign;
    }

    /**
     * @return CheckInUser[]
     * 获取所有开启签到提醒的用户
     */
    public function getCheckInUserByRemind()
    {
        $checkInUserId = CheckInUserRemind::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->andWhere(['like', 'date', date('Y-m-d')])
            ->select('user_id');
        $checkInUser = CheckInUser::find()->alias('cu')->with('user')
            ->where(['cu.is_delete' => 0, 'cu.mall_id' => $this->mall->id, 'cu.is_remind' => 1])
            ->andWhere(['not in', 'cu.user_id', $checkInUserId])
            ->leftJoin(['f' => Formid::tableName()], 'f.user_id = cu.user_id')
            ->andWhere(['>', 'f.remains', 0])
            ->andWhere(['!=', 'f.form_id', 'null'])->groupBy('cu.user_id')->all();

        return $checkInUser;
    }

    /**
     * @param $configTime
     * @return false|int
     * 获取最近一次签到提醒时间
     */
    public function getRemind($configTime = null)
    {
        if (!$configTime) {
            $configTime = $this->config->time;
        }
        $time = time();
        $delay = strtotime(date('Y-m-d', $time) . $configTime);
        if ($delay < $time) {
            $delay = strtotime(date('Y-m-d', $time + 86400) . $configTime);
        }
        if (!$delay) {
            $delay = time() + 86400;
        }
        return $delay - $time;
    }

    /**
     * @param $attributes
     * @return bool
     * @throws \Exception
     * 添加已提醒的用户
     */
    public function addCheckInUserRemind($attributes)
    {
        $form = new CheckInUserRemind();
        $form->attributes = $attributes;
        if (!$form->save()) {
            throw new \Exception($this->getErrorMsg($form));
        }
        return true;
    }

    /**
     * @param int $time
     * 添加签到提醒定时任务
     */
    public function addRemindJob($time = -1)
    {
        if ($time < 0) {
            $time = $this->getRemind();
        }
        \Yii::$app->queue->delay($time)->push(new RemindJob([
            'mall' => $this->mall
        ]));
    }

    /**
     * @param $month
     * @param $year
     * @param $user
     * @return array
     * 获取指定用户指定月份已签到的日期
     */
    public function getDay($month, $user, $year = null)
    {
        if (!$user) {
            return [];
        }
        /* @var CheckInSign[] $checkInAll */
        $checkInAll = $this->getCheckInDayByMonth($month, $user, $year);
        $checkInDay = [];
        foreach ($checkInAll as $item) {
            $checkInDay[] = intval(date('j', strtotime($item->created_at)));
        }

        return $checkInDay;
    }

    /**
     * @param $user
     * @return CommonTemplate
     * 获取订阅消息发送方法
     */
    public function getCommonTemplate($user)
    {
        $template = new CommonTemplate();
        $template->user = $user;
        $template->page = 'plugins/check_in/index/index';
        return $template;
    }

    /**
     * @param CheckInUser $checkInUser
     * @param $attributes
     * @throws \Exception
     * @return bool
     */
    public function saveCheckInUser($checkInUser, $attributes)
    {
        $checkInUser->attributes = $attributes;
        if (!$checkInUser->save()) {
            throw new \Exception($this->getErrorMsg($checkInUser));
        }
        return true;
    }

    /**
     * @param bool $all 是否显示全部
     * @param int $limit
     * @param int $page
     * @return array
     * 获取签到插件的所有用户
     */
    public function getCheckInUserAll($limit = 20, $page = 1)
    {
        $checkInUser = CheckInUser::find()->with('user')
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->page($pagination, $limit, $page)->all();
        return [
            'list' => $checkInUser,
            'pagination' => $pagination
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @return int
     * 已1000条数据为周期进行清除用户连续签到数
     */
    public function clearContinue($page = 1, $count = 0)
    {
        $res = $this->getCheckInUserAll(1000, $page);
        /* @var CheckInUser[] $checkInUserList */
        $checkInUserList = $res['list'];
        /* @var Pagination $pagination */
        $pagination = $res['pagination'];
        foreach ($checkInUserList as $checkInUser) {
            if ($this->getSignInByToday($checkInUser->user)) {
                $checkInUser->continue = 1;
                if (strtotime($checkInUser->continue_start) < strtotime(date('Y-m-d'))) {
                    $checkInUser->continue_start = mysql_timestamp();
                }
            } else {
                $checkInUser->continue = 0;
                $checkInUser->continue_start = '';
            }
            if ($checkInUser->save()) {
                $count++;
            } else {
                \Yii::error($checkInUser->errors);
            }
        }
        if ($pagination->page_count > $page) {
            $page++;
            $this->clearContinue($page, $count);
        }
        return $count;
    }

    /**
     * @param $continueType
     * @return MonthState|UnlimitedState|WeekState|null
     * @throws \Exception
     * 根据清除连续签到的状态获取不同的处理类
     */
    public function getContinueTypeClass($continueType)
    {
        $state = null;
        switch ($continueType) {
            case 1:
                $state = new UnlimitedState();
                break;
            case 2:
                $state = new WeekState();
                break;
            case 3:
                $state = new MonthState();
                break;
            default:
                throw new \Exception('错误的清除连续签到状态码');
        }
        $state->common = $this;
        return $state;
    }

    public static function getDefault()
    {
        $plugin = new Plugin();
        return [
            'remind_font' => '#FFFFFF',
            'daily_font' => '#FFFFFF',
            'prompt_font' => '#FFFFFF',
            'btn_bg' => '#cdcdcd',
            'not_prompt_font' => '#ffffff',
            'not_btn_bg' => '#5997fc',
            'line_font' => '#5997fc',
            'end_bg' => '#283777',
            'end_style' => '0',

            'not_signed_icon' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/check-in.png',
            'signed_icon' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/over.png',
            'head_bg' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/top-bg.png',
            'balance_icon' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/red.png',
            'integral_icon' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/integral.png',
            'calendar_icon' => PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/choose.png',
            'end_gradient_bg' => '#FFFFFF',
        ];
    }

    public function getCustomize()
    {
        $model = CheckInCustomize::findOne([
            'mall_id' => $this->mall->id,
            'name' => 'page',
        ]);
        if (!$model) {
            return self::getDefault();
        }
        $value = json_decode($model->value, true);
        return array_merge(self::getDefault(), $value);
    }

    public function setCustomize($value)
    {
        $model = CheckInCustomize::findOne([
            'name' => 'page',
            'mall_id' => $this->mall->id,
        ]);
        if (!$model) {
            $model = new CheckInCustomize();
            $model->name = 'page';
            $model->mall_id = $this->mall->id;
        }
        $model->value = \Yii::$app->serializer->encode($value);
        return $model->save();
    }

    /**
     * @param $status
     * @param $day
     * @param CheckInUser $checkInUser
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getSignInByContinue($status, $day, $checkInUser)
    {
        $sign = CheckInSign::find()->where([
            'mall_id' => $this->mall->id, 'user_id' => $checkInUser->user_id, 'status' => $status, 'day' => $day,
            'is_delete' => 0
        ])->andWhere(['<=', 'day', $checkInUser->continue])
            ->keyword($checkInUser->continue_start, ['>=', 'created_at', $checkInUser->continue_start])->one();
        return $sign;
    }

    /**
     * @param $status
     * @return BaseAward
     * @throws \Exception
     */
    public function getAward($status)
    {
        switch ($status) {
            case 1:
                $award = new NormalAward();
                break;
            case 2:
                $award = new ContinueAward();
                break;
            case 3:
                $award = new TotalAward();
                break;
            default:
                throw new \Exception('错误的参数');
        }
        $award->common = $this;
        return $award;
    }
}
