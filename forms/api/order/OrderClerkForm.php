<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\order\CommonOrderClerk;
use app\models\ClerkUser;
use app\models\Model;
use app\models\Order;
use Helper\Api;

class OrderClerkForm extends Model
{
    public $id;
    public $clerk_remark;
    public $action_type; // 1.小程序端确认收款 | 2.后台确认收款

    public function rules()
    {
        return [
            [['id', 'action_type'], 'integer'],
            [['id', 'action_type'], 'required'],
            [['clerk_remark'], 'string'],
        ];
    }

    /**
     * 确认支付
     */
    public function affirmPay()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $commonOrderClerk = new CommonOrderClerk();
            $commonOrderClerk->id = $this->id;
            $commonOrderClerk->action_type = $this->action_type;
            $commonOrderClerk->clerk_id = \Yii::$app->user->id;
            $commonOrderClerk->clerk_type = 1;
            $res = $commonOrderClerk->affirmPay();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '收款成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function orderClerk()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $commonOrderClerk = new CommonOrderClerk();
            $commonOrderClerk->id = $this->id;
            $commonOrderClerk->action_type = $this->action_type;
            $commonOrderClerk->clerk_remark = $this->clerk_remark;
            $commonOrderClerk->clerk_id = \Yii::$app->user->id;
            $commonOrderClerk->clerk_type = 1;
            $res = $commonOrderClerk->orderClerk();

            //权限判断，用以核销后返回的页面判断
            $is_clerk = 1;
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (empty(\Yii::$app->plugin->getInstalledPlugin('clerk')) || !in_array('clerk', $permission) || empty(ClerkUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]))) {
                $is_clerk = 0;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '核销成功',
                'data' => [
                    'is_clerk' => $is_clerk
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function qrClerkCode()
    {
        try {
            /** @var Order $order */
            $order = Order::find()->where([
                'id' => $this->id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'send_type' => 1,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->is_pay == 0 && $order->pay_type != 2) {
                throw new \Exception('订单未支付');
            }

            $qrCode = new CommonQrCode();
            $res = $qrCode->getQrCode(['id' => $this->id], 100, 'pages/order/clerk/clerk');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
