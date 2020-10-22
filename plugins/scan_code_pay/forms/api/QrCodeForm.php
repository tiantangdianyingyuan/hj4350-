<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\Model;

class QrCodeForm extends Model
{
    public $price;

    public function rules()
    {
        return [
            [['price'], 'number']
        ];
    }

    public function attributeLabels()
    {
        return [
            'price' => '金额'
        ];
    }

    public function getQrCode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $commonQrCode = new CommonQrCode();
        if ($this->price) {
            $res = $commonQrCode->getQrCode(['price' => round($this->price, 2), 'user_id' => \Yii::$app->user->id], 240, 'plugins/scan_code/index/index');
        } else {
            $res = $commonQrCode->getQrCode([], 240, 'plugins/scan_code/index/index');
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'qr_code' => $res
            ]
        ];
    }
}