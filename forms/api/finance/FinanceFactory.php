<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/2
 * Time: 13:39
 */

namespace app\forms\api\finance;

use app\core\response\ApiCode;
use app\models\Finance;
use app\models\Model;
use app\models\OrderSubmitResult;

class FinanceFactory extends Model
{
    /**
     * @param $operate
     * @return BaseFinanceCashForm
     * @throws \Exception
     */
    public function create($operate)
    {
        $plugin = "app\\plugins\\{$operate}\\Plugin";
        if (!class_exists($plugin)) {
            throw new \Exception('未安装' . $operate . '插件');
        }
        $plugin = new $plugin();
        return $plugin;
    }

    public function save()
    {
        try {
            /* @var BaseFinanceCashForm $class */
            $operate = \Yii::$app->request->post('model');
            $plugin = $this->create($operate);
            if (!method_exists($plugin, 'getApiCashForm')) {
                throw new \Exception('插件' . $operate . '不支持该功能');
            }
            $class = $plugin->getApiCashForm();
            $class->attributes = \Yii::$app->request->post();
            return $class->save();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => $exception
            ];
        }
    }

    public function result()
    {
        $queueId = \Yii::$app->request->get('queue_id');
        if (!\Yii::$app->queue->isDone($queueId)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'retry' => 1,
                ],
            ];
        }
        $token = \Yii::$app->request->get('token');
        $cash = Finance::find()->where([
            'order_no' => $token, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id
        ])->one();
        if (!$cash) {
            $result = OrderSubmitResult::findOne([
                'token' => $token,
            ]);
            if ($result) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $result->data,
                ];
            }
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现申请不存在或已失效。',
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '提现申请提交成功'
        ];
    }

    public function config()
    {
        try {
            /* @var BaseFinanceConfig $class */
            $operate = \Yii::$app->request->post('model');
            $plugin = $this->create($operate);
            if (!method_exists($plugin, 'getFinanceConfig')) {
                throw new \Exception('插件' . $operate . '不支持该功能');
            }
            $class = $plugin->getFinanceConfig();
            return $class->config();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => $exception
            ];
        }
    }
}
