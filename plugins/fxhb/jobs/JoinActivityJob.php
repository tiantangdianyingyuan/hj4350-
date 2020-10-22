<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 14:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\jobs;


use app\models\Mall;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\fxhb\events\JoinActivityEvent;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\handle\HandlerRegister;
use app\plugins\fxhb\models\FxhbActivity;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property User $user
 */
class JoinActivityJob extends BaseObject implements JobInterface
{
    public $user_activity_id;
    public $mall;
    public $user;
    public $token;

    public $parentActivity;

    public function execute($queue)
    {
        $this->mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($this->mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $common = CommonFxhbDb::getCommon($this->mall);
            $this->parentActivity = null;
            if ($this->user_activity_id) {
                // 帮拆红包
                $userActivity = $this->joinUserActivity($common);
            } else {
                // 发起拆红包
                $userActivity = $this->joinActivity($common);
            }
            \Yii::$app->trigger(HandlerRegister::FXHB_JOIN_ACTIVITY, new JoinActivityEvent([
                'userActivity' => $userActivity,
                'parentActivity' => $this->parentActivity ? $this->parentActivity : $userActivity,
                'mall' => $this->mall
            ]));
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            $form = new OrderSubmitResult();
            $form->token = $this->token;
            $form->data = $exception->getMessage();
            $form->save();
        }
    }

    /**
     * @param CommonFxhbDb $common
     * @return mixed
     * @throws \Exception
     */
    private function joinUserActivity($common)
    {
        // 帮拆红包
        $parentActivity = $common->getUserActivityById($this->user_activity_id, null);
        $this->parentActivity = $parentActivity;
        if (!$parentActivity) {
            throw new \Exception('分享的用户未发起红包活动，或者已结束');
        }
        $config = $common->getActivity();
        if (!$config) {
            throw new \Exception('活动未开启');
        }
        if ($parentActivity->status != 0) {
            return $parentActivity;
        }
        if ($parentActivity->fxhb_activity_id != $config->id) {
            throw new \Exception('活动已结束');
        }
        if ($config->start > time()) {
            throw new \Exception('活动暂未开始');
        }
        if ($config->end < time()) {
            throw new \Exception('活动已经结束');
        }
        $nowUserActivity = $common->getUserActivityByUserId($this->user->id, $config->id);
        if ($nowUserActivity) {
            throw new \Exception('用户正在参与活动中');
        }
        if ($parentActivity->user_id == $this->user->id) {
            throw new \Exception('本人不能拆自己的红包');
        }
        if (!$parentActivity->activity || $parentActivity->activity->end < time()
            || $parentActivity->activity->status == 0) {
            throw new \Exception('分享用户发起的拆红包已结束');
        }
        $activity = \Yii::$app->serializer->decode($parentActivity->data);
        $resetTime = $common->getResetTime($parentActivity);
        if ($resetTime == 0) {
            throw new \Exception('分享用户发起的拆红包已到期');
        }
        if ($activity->help_num >= 0) {
            $helpNum = $common->getHelpNum($this->user->id, $parentActivity->fxhb_activity_id);
            if ($helpNum >= $activity->help_num) {
                throw new \Exception('用户帮忙拆红包次数已达上限，无法再次帮助拆红包');
            }
        }
        $userActivityJoin = $common->getUserActivityByParentId($parentActivity->id, $this->user->id);
        if ($userActivityJoin) {
            throw new \Exception('您已经帮助分享的用户拆红包，不可再次帮助Ta拆红包');
        }

        $attributes = [
            'user_id' => $this->user->id,
            'parent_id' => $parentActivity->id,
            'fxhb_activity_id' => $parentActivity->fxhb_activity_id,
            'number' => $parentActivity->number,
            'count_price' => $parentActivity->count_price,
            'status' => 0,
            'mall_id' => $parentActivity->mall_id,
            'data' => $parentActivity->data,
            'token' => $this->token
        ];
        $userActivity = $common->joinActivity($attributes);
        return $userActivity;
    }


    /**
     * @param CommonFxhbDb $common
     * @return mixed
     * @throws \Exception
     */
    private function joinActivity($common)
    {
        // 发起拆红包
        /* @var FxhbActivity $activity */
        $activity = $common->getActivity();
        if (!$activity) {
            throw new \Exception('拆红包活动暂未开放');
        }
        if ($activity->start > time()) {
            throw new \Exception('活动暂未开始');
        }
        if ($activity->end < time()) {
            throw new \Exception('活动已经结束');
        }
        if ($activity->sponsor_num == 0) {
            throw new \Exception('活动已经结束');
        }
        if ($activity->sponsor_num >= 0) {
            $sponsorNum = $common->getSponsorNum($this->user->id, $activity->id);
            if ($sponsorNum >= $activity->sponsor_num) {
                throw new \Exception('用户发起次数已达上限，不能发起');
            }
        }
        if ($activity->sponsor_count == 0) {
            throw new \Exception('活动红包已抢空，请下次再来');
        }
        $nowUserActivity = $common->getUserActivityByUserId($this->user->id, $activity->id);
        if ($nowUserActivity) {
            throw new \Exception('您已有一个红包活动进行中');
        }
        $attributes = [
            'user_id' => $this->user->id,
            'parent_id' => 0,
            'fxhb_activity_id' => $activity->id,
            'number' => $activity->number,
            'count_price' => $activity->count_price,
            'status' => 0,
            'mall_id' => $this->mall->id,
            'data' => \Yii::$app->serializer->encode($activity->attributes),
            'token' => $this->token
        ];
        if ($activity->sponsor_count_type == 1) {
            // 拆红包发起时减掉拆红包数量
            $common->updateSponsorCount($activity, 'sub');
        }
        $userActivity = $common->joinActivity($attributes);
        $resetTime = $common->getResetTime($userActivity);
        \Yii::$app->queue->delay($resetTime)->push(new UserActivityTimerJob([
            'userActivity' => $userActivity,
            'mall' => $this->mall
        ]));
        return $userActivity;
    }
}
