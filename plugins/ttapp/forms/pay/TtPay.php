<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/7
 * Time: 15:04
 */

namespace app\plugins\ttapp\forms\pay;

use Alipay\AlipayRequestFactory;
use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\helpers\CurlHelper;

class TtPay
{
    public $appId;
    public $secret;
    public $merchant_id;
    public $alipay_app_id;
    public $alipay_public_key;
    public $alipay_private_key;
    public $curl;
    public $data;
    public $order_result;
    public $tt_sign;
    public $ip;

    const UNIFIED_ORDER = 'https://tp-pay.snssdk.com/gateway';

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        $this->curl = CurlHelper::getInstance();
        $this->ip = \Yii::$app->request->userIP;
    }

    public function genData($param)
    {
        $this->unifiedOrder($param);

        if ($this->order_result['response']['code'] != '10000') {
            throw new \Exception($this->order_result['response']['sub_msg']);
        }
        $this->data['sign'] = $this->tt_sign;
        $this->data['tt_trade_no'] = $this->order_result['response']['trade_no'];
        $this->data['alipay_amount'] = $param['alipay_amount'];

        $newdata = [
          'data' => [
              'app_id' => $this->data['app_id'],
              'sign_type' => 'MD5',
              'timestamp' => (string)time(),
              'trade_no' => $this->data['tt_trade_no'],
              'merchant_id' => $this->data['biz_content']['merchant_id'],
              'uid' => $this->data['biz_content']['uid'],
              'total_amount' => $this->data['biz_content']['total_amount'],
          ]
        ];

        $orderStr = $this->genAliParams($this->data);

        $newdata['data']['params'] = json_encode(['url' => $orderStr]);
        list($string,$sign) = $this->makeSign($newdata['data']);
        $newdata['data']['method'] = 'tp.trade.confirm';
        $newdata['data']['pay_channel'] = 'ALIPAY_NO_SIGN';
        $newdata['data']['pay_type'] = 'ALIPAY_APP';
        $newdata['data']['risk_info'] = json_encode(['ip' => $this->ip]);
        $newdata['data']['sign'] = $sign;

        return $newdata;
    }

    public function unifiedOrder($param)
    {
        $this->data = [
            'app_id' =>$this->appId,
            'method' => "tp.trade.create",
            'charset' => "utf-8",
            'sign_type' => "MD5",
            'timestamp' => time(),
            'version' => "1.0",
            'biz_content' => [
                'out_order_no' => '',
                'uid' => '',
                'merchant_id' => $this->merchant_id,
                'total_amount' => '',
                'currency' => 'CNY',
                'product_code' => 'QUICK_MSECURITY_PAY',//支付宝那边必传的参数
                'subject' => '这是一条订单',
                'body' => '这是订单详情',
                'trade_time' => time(),
                'valid_time' => 86400,
                'notify_url' => '',
                'risk_info' => [
                    'ip' => $this->ip
                ],
            ],
        ];
        $this->data['biz_content'] = array_merge($this->data['biz_content'],$param);
        list($post,$ret) = $this->makeSign($this->data);
        $post = $this->build($post,$ret);
        $url = self::UNIFIED_ORDER."?".$post;
        try {
            $data = array_merge($this->data, [
                'sign' => $ret,
            ]);
            if (is_array($data['biz_content'])) $data['biz_content'] = json_encode($data['biz_content']);
            $res = $this->curl->httpPost($url, null, $data);
        } catch (\Exception $e) {
            \Yii::error($e);
        }
        $this->order_result = $res;
        $this->tt_sign = $ret;
    }

    public function genAliParams($data)
    {
        $aop = new AopClient(
            $this->alipay_app_id,
            AlipayKeyPair::create($this->alipay_private_key, $this->alipay_public_key)
        );
        $request = AlipayRequestFactory::create('alipay.trade.app.pay', [
            'notify_url' => $data['biz_content']['notify_url'],
            'biz_content' => [
                'body' => $data['biz_content']['subject'],
                'subject' => $data['biz_content']['subject'],
                'out_trade_no' => $data['biz_content']['out_order_no'],
                'total_amount' => $data['biz_content']['alipay_amount'],
                'product_code' => 'QUICK_MSECURITY_PAY',
            ],
        ]);

        try {
            $orderStr = $aop->sdkExecute($request);
        } catch (\Exception $ex) {
            http_response_code(500);
            print_r($ex);
        }

        return $orderStr;
    }

    public function build($data,$ret)
    {
        $pos = strpos($data,'&sign_type');
        $newstring = substr_replace($data,"&sign={$ret}",$pos ,0);
        return  $newstring;
    }

    public function makeSign($args)
    {
        if (isset($args['biz_content'])) {
            $args['biz_content'] = json_encode($args['biz_content']);
        }
        ksort($args);
        $parts = array();
        foreach ($args as $k => $v) {
            $parts[] = $k . '=' . $v;
        }

        $string = implode('&', $parts);
        $newstring = $string . $this->secret;
        $result = md5($newstring);
        return [$string,$result];
    }
}