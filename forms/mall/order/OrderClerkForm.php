<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\order\CommonOrderClerk;
use app\models\Model;

class OrderClerkForm extends Model
{
    public $order_id;
    public $clerk_id;
    public $clerk_remark;
    public $action_type; // 1.小程序端确认收款 | 2.后台确认收款

    public function rules()
    {
        return [
            [['order_id', 'action_type', 'clerk_id'], 'required'],
            [['clerk_id', 'order_id', 'action_type'], 'integer'],
            [['clerk_remark'], 'string'],
        ];
    }

    /**
     * 确认支付
     */
    public function affirmPay()
    {
        try {
            $commonOrderClerk = new CommonOrderClerk();
            $commonOrderClerk->id = $this->order_id;
            $commonOrderClerk->action_type = $this->action_type;
            $commonOrderClerk->clerk_id = $this->clerk_id;
            $commonOrderClerk->clerk_type = 2;
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
        try {
            $commonOrderClerk = new CommonOrderClerk();
            $commonOrderClerk->id = $this->order_id;
            $commonOrderClerk->action_type = $this->action_type;
            $commonOrderClerk->clerk_remark = $this->clerk_remark;
            $commonOrderClerk->clerk_id = $this->clerk_id;
            $commonOrderClerk->clerk_type = 2;
            $res = $commonOrderClerk->orderClerk();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '核销成功',
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
}
