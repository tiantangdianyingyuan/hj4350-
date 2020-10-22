<?php


namespace app\jobs;


use app\forms\common\CommonOption;
use yii\queue\JobInterface;

class RunQueueShJob implements JobInterface
{
    public $time;
    public function execute($queue)
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
            1 => array("pipe", "w"),  // 标准输出，重定向子进程输入到主进程STDOUT
            2 => array("file", "error-output.txt", "a") // 标准错误，写入到一个文件
        );
        $queueFile = dirname(__DIR__) . '/queue.sh';
        $process = proc_open('chmod a+x ' . $queueFile . ' && ' . $queueFile, $descriptorspec, $pipes, __DIR__);
        proc_close($process);
        CommonOption::set($this->getKey(), intval($this->time));
    }


    public function valid()
    {
        $result = CommonOption::get($this->getKey());
        return intval($result) === intval($this->time);
    }

    private function getKey()
    {
        return 'test_queue3_service_job_time';
    }
}
