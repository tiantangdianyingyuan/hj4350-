<?php

namespace app\forms\common\order\send;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\forms\common\order\send\BaseSend;

class CitySendForm extends BaseSend
{
    public $man;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['man'], 'required'],
            [['man'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'man' => '配送员',
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $order = $this->getOrder();
            $this->saveOrderDetailExpress($order);

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功',
            ];

        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    public function saveExtraData($orderDetailExpress)
    {
        // 暂不支持第三方修改配送
        if ($this->express_id && $orderDetailExpress->send_type == 1) {
            throw new \Exception('该订单已由第三方配送公司配送，暂不支持修改');
        }

        // 从字符串中截取配送员id
        $id = substr($this->man, 1, strpos($this->man, ')') - 1);
        $deliveryman = CommonDelivery::getInstance()->getManOne($id);
        if (!$deliveryman) {
            throw new \Exception('所选配送员不存在');
        }

        $cityInfo = [
            'city_info' => [
                'id' => $deliveryman->id,
                'name' => $deliveryman->name,
                'mobile' => $deliveryman->mobile,
            ],
        ];

        $orderDetailExpress->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
        $orderDetailExpress->city_name = $deliveryman->name;
        $orderDetailExpress->city_mobile = $deliveryman->mobile;
        $orderDetailExpress->send_type = 2;
        $orderDetailExpress->express_type = '同城配送';
    }
}
