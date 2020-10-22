<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderRefund;

class CancelRefundForm extends Model
{
    public $refund_id;

    public function rules()
    {
        return [
            [['refund_id'], 'required'],
            [['refund_id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'refund_id' => "售后订单ID"
        ];
    }

    public function cancelRefund()
    {
        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->refund_id
            ])->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            if ($orderRefund->status != 1) {
                throw new \Exception($orderRefund->status == 2 ? '商家已同意售后申请' : '商家已拒绝售后申请');
            }

            $orderRefund->is_delete = 1;
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '申请已撤销'
            ];
        }catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}