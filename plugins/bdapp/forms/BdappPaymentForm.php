<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/7/30
 * Time: 16:22
 */

namespace app\plugins\bdapp\forms;


use app\models\Model;

class BdappPaymentForm extends Model
{
    public $privateKeyFile;
    public $publicKeyFile;
    public $appKey;
    public $dealId;

    public function getAppPayData($options)
    {
        $options['amount'] = $options['amount']*100;
        $requestParamsArr = array(
            "appKey"=> $this->appKey,
            "dealId"=> $this->dealId,
            "tpOrderId"=> $options['order_no'],
            "totalAmount" => (string)$options['amount'],
        );

        $rsaSign = RsaSign::genSignWithRsa($requestParamsArr, $this->privateKeyFile);
        $requestParamsArr['sign'] = $rsaSign;

        return [
            "orderInfo" => [
                "dealId"=> $this->dealId,
                "appKey"=> $this->appKey,
                "totalAmount"=> (string)$options['amount'],
                "tpOrderId"=> $options['order_no'],
                "dealTitle"=> $options['title'],
                "signFieldsRange"=> "1",
                "rsaSign"=> $rsaSign,
                "bizInfo"=> [
                    "tpData"=>[
                        "dealId"=> $this->dealId,
                        "appKey"=> $this->appKey,
                    ],
                ]
            ]
        ];
    }
}
