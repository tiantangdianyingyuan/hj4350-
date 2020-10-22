<?php

/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 */


namespace app\plugins\gift\jobs;


use app\models\Mall;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftLog;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GiftTimeOpenJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var GiftLog $gift_log_info */
    public $gift_log_info;

    /***
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);

        $t = \Yii::$app->db->beginTransaction();
        try {
            \Yii::error('定时开奖队列开始');
            CommonGift::openGift($this->gift_log_info->id, $this->gift_log_info, 'time_open');

            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error('礼物定时抽奖队列错误');
            \Yii::error($e->getMessage());
            \Yii::error($e);
            throw $e;
        }
    }
}
