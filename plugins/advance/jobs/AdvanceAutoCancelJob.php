<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/9/6
 * Email: <657268722@qq.com>
 */

namespace app\plugins\advance\jobs;


use app\models\GoodsAttr;
use app\plugins\advance\models\AdvanceOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AdvanceAutoCancelJob extends BaseObject implements JobInterface
{
    /* @var $model AdvanceOrder */
    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        if (!$this->id) {
            return;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $model = AdvanceOrder::findOne(['id' => $this->id, 'is_delete' => 0]);
            if (empty($model)) {
                throw new \Exception('订单不存在');
            }
            if ($model->is_pay == 1) {//已支付,跳过
                throw new \Exception('订单已支付');
            }
            $model->is_cancel = 1;
            $model->cancel_time = date('Y-m-d H:i:s', time());
            (new GoodsAttr())->updateStock($model->goods_num, 'add', $model->goods_id, $model->goods_attr_id);//返回库存
            if (!$model->save()) {
                throw new \Exception($model->errors);
            }
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error("预售定金订单自动取消:" . $exception->getMessage() . $exception->getFile() . $exception->getLine());
        }
    }
}