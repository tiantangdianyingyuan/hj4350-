<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 18:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\jobs;


use app\models\Mall;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbUserActivity;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property FxhbUserActivity $userActivity
 */
class UserActivityTimerJob extends BaseObject implements JobInterface
{
    public $userActivity;
    public $mall;

    public function execute($queue)
    {
        $this->mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($this->mall);
        $this->userActivity = FxhbUserActivity::findOne($this->userActivity->id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->userActivity->status != 0) {
                return true;
            }
            $common = CommonFxhbDb::getCommon($this->mall);
            $userActivityAll = $common->getUserActivityAllById($this->userActivity->id);
            foreach ($userActivityAll as $userActivity) {
                $common->setUserActivityStatus($userActivity, 2);
            }
            /* @var FxhbActivity $activity */
            $activity = \Yii::$app->serializer->decode($this->userActivity->data);
            if ($activity->sponsor_count_type == 1) {
                // 拆红包失败时加上拆红包数量
                $common->updateSponsorCount($this->userActivity->activity, 'add');
            }
            // TODO 订阅消息发送

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }
}
