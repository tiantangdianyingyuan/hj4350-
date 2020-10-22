<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\QrCodeParameter;

class QrCodeForm extends Model
{
    public $token;

    public function rules()
    {
        return [
            [['token'], 'required'],
            [['token'], 'string'],
        ];
    }

    public function getParameter()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = QrCodeParameter::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'token' => $this->token
        ])->one();

        if ($detail) {
            $detail['data'] = $detail['data'] ? \Yii::$app->serializer->decode($detail['data']) : [];
            if ($detail['data']) {
                $detail['data']['qr_code_id'] = $detail->id;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail
            ]
        ];
    }
}
