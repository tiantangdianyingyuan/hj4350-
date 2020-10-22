<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/8
 * Time: 18:04
 */

namespace app\forms\common\convert;


use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class TestQueueJob extends BaseObject implements JobInterface
{
    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        \Yii::warning('TestQueueJob Execute At --->' . date('Y-m-d H:i:s'));
        file_put_contents(__DIR__ . '/test-queue-job.txt', 'test-queue-job');
    }

    public function checkJob()
    {
        if (file_exists(__DIR__ . '/test-queue-job.txt')) {
            unlink(__DIR__ . '/test-queue-job.txt');
            return true;
        }
        return false;
    }
}
