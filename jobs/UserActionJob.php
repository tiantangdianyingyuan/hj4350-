<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/14
 * Time: 15:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\models\CoreActionLog;
use yii\base\Component;
use yii\queue\JobInterface;

class UserActionJob extends Component implements JobInterface
{
    public $newBeforeUpdate;
    public $newAfterUpdate;
    public $modelName;
    public $modelId;
    public $remark;

    public $user_id;
    public $mall_id;

    public function execute($queue)
    {
        try {
            $form = new CoreActionLog();
            $form->mall_id = $this->mall_id;
            $form->user_id = $this->user_id;
            $form->model_id = $this->modelId;
            $form->model = $this->modelName;
            $form->before_update = \Yii::$app->serializer->encode($this->newBeforeUpdate);
            $form->after_update = \Yii::$app->serializer->encode($this->newAfterUpdate);
            $form->remark = $this->remark ?: '数据更新';
            $res = $form->save();

            \Yii::warning('操作日志存储成功,日志ID:' . $form->id);
            return $res;
        } catch (\Exception $e) {
            \Yii::error('操作日志存储失败,日志ID:' . $form->id);
            \Yii::error($e->getMessage());
        }
    }
}
