<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_send_template;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderSendTemplateAddress;

class AddressForm extends Model
{
    public function rules()
    {
        return [];
    }

    public function address()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $address = OrderSendTemplateAddress::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->one();

        if ($address) {
            $address = (new OrderSendTemplateAddress())->getNewData($address);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $address,
            ]
        ];
    }
}