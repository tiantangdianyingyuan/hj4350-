<?php

/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 */


namespace app\plugins\gift\jobs;


use app\models\Mall;
use app\models\User;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOpenResult;
use app\plugins\gift\models\GiftUserOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GiftOpenJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    public $gift_id;
    public $appVersion;
    public $token;

    /***
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!empty(GiftUserOrder::findOne(['gift_id' => $this->gift_id, 'user_id' => $this->user->id]))) {
                throw new \Exception('重复参与活动');
            }
            $gift_log_info = GiftLog::findOne(['id' => $this->gift_id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
            if (empty($gift_log_info)) {
                throw new \Exception('礼物不存在');
            }
//            if ($gift_log_info->user_id == \Yii::$app->user->id) {
//                throw new \Exception('自己不能领取自己的礼物');
//            }
            //参与抽礼物
            $user_order_model = new GiftUserOrder();
            $user_order_model->mall_id = $this->mall->id;
            $user_order_model->user_id = $this->user->id;
            $user_order_model->gift_id = $this->gift_id;
            $user_order_model->token = $this->token;
            if (!$user_order_model->save()) {
                throw new \Exception($user_order_model->errors[0]);
            }
            switch ($gift_log_info->type) {
                case 'direct_open':
                    //直接送礼物
                    CommonGift::openGift($this->gift_id, $gift_log_info, 'direct_open', \Yii::$app->user->id);
                    break;
                case 'num_open':
                    //满人送
                    if ($gift_log_info->is_confirm == 1) {
                        throw new \Exception('礼物已被抢光');
                    }
                    $count = GiftUserOrder::find()
                        ->where(['mall_id' => \Yii::$app->mall->id, 'gift_id' => $this->gift_id, 'is_turn' => 0, 'is_delete' => 0])
                        ->count();
                    if ($gift_log_info->open_num == $count) {
                        CommonGift::openGift($this->gift_id, $gift_log_info, 'num_open');
                    } elseif ($count > $gift_log_info->open_num) {
                        throw new \Exception('礼物已被抢光');
                    }
                    break;
                case 'time_open':
                    //到期送
                    if ($gift_log_info->is_confirm == 1) {
                        throw new \Exception('礼物已被抢光');
                    }
                    if (strtotime($gift_log_info->open_time) < time()) {
                        throw new \Exception('该礼物已过期');
                    }
                    break;
            }

            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            $orderSubmitResult = new GiftOpenResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
            \Yii::error('礼物抽（领）奖队列错误');
            \Yii::error($e->getMessage());
            \Yii::error($e);
            throw $e;
        }
    }
}
