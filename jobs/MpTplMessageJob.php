<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\jobs;

use app\forms\common\CommonOption;
use app\forms\common\mptemplate\MpTplMessage;
use app\models\MpTemplateRecord;
use app\models\Option;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class MpTplMessageJob extends BaseObject implements JobInterface
{
    public $mall;
    public $token;
    public $url;
    public $templateId;
    public $data;
    public $miniprogram;

    public $admin_open_list = [];
    public $app_id;
    public $app_secret;
    /**
     * @param \yii\queue\Queue $queue
     * @throws \Exception
     */
    public function execute($queue)
    {
        try {
            $logs = [];
            $count = 0;

            foreach ($this->admin_open_list as $item) {
                //发送数据
                $args = [
                    'touser' => $item['open_id'],
                    'template_id' => $this->templateId,
                    'data' => $this->data,
                ];

                $log = [
                    'open_id' => $args['touser'],
                    'mall_id' => $this->mall->id,
                    'status' => 1,
                    'data' => \Yii::$app->serializer->encode($args),
                    'error' => '',
                    'created_at' => mysql_timestamp(),
                    'token' => $this->token,
                ];

                try {
                    (new MpTplMessage())->senderMsg($args, [
                        'app_id' => $this->app_id,
                        'app_secret' => $this->app_secret,
                        'mall_id' => $this->mall->id,
                    ]);
                } catch (\Exception $e) {
                    $log['error'] = $e->getMessage();
                    $log['status'] = 0;
                }
                $count++;
                $logs[] = $log;
            }

            if ($count > 0) {
                \Yii::$app->db->createCommand()->batchInsert(
                    MpTemplateRecord::tableName(),
                    ['open_id', 'mall_id', 'status', 'data', 'error', 'created_at', 'token'],
                    $logs
                )->execute();
            }
        } catch (\Exception $e) {
            \Yii::warning($e);
            throw $e;
        }
    }
}
