<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/5 11:52
 */


namespace luweiss\Wechat;


class WechatPay extends WechatBase
{
    const SIGN_TYPE_MD5 = 'MD5';
    const TRADE_TYPE_JSAPI = 'JSAPI';
    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_APP = 'APP';
    const TRADE_TYPE_MWEB = 'MWEB';

    public $appId;
    public $mchId;
    public $key;
    public $certPemFile;
    public $keyPemFile;

    /**
     * WechatPay constructor.
     * @param array $config ['appId', 'mchId', 'key', 'certPemFile', 'keyPemFile']
     */
    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */
    protected function getClientResult($result)
    {
        if (!isset($result['return_code'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['return_code'] !== 'SUCCESS') {
            $msg = 'returnCode: ' . $result['return_code'] . ', returnMsg: ' . $result['return_msg'];
            throw new WechatException($msg, 0, null, $result);
        }
        if (!isset($result['result_code'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['result_code'] !== 'SUCCESS') {
            $msg = 'errCode: ' . $result['err_code'] . ', errCodeDes: ' . $result['err_code_des'];
            throw new WechatException($msg, 0, null, $result);
        }
        return $result;
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws WechatException
     */
    protected function send($api, $args)
    {
        $args['appid'] = !empty($args['appid']) ? $args['appid'] : $this->appId;
        $args['mch_id'] = !empty($args['mch_id']) ? $args['mch_id'] : $this->mchId;
        $args['nonce_str'] = !empty($args['nonce_str']) ? $args['nonce_str'] : md5(uniqid());
        $args['sign'] = $this->makeSign($args);
        $xml = WechatHelper::arrayToXml($args);
        $res = $this->getClient()->setDataType(WechatHttpClient::DATA_TYPE_XML)->post($api, $xml);
        return $this->getClientResult($res);
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws WechatException
     */
    protected function sendWithPem($api, $args)
    {
        $args['nonce_str'] = !empty($args['nonce_str']) ? $args['nonce_str'] : md5(uniqid());
        $args['sign'] = $this->makeSign($args);
        $xml = WechatHelper::arrayToXml($args);
        $res = $this->getClient()
            ->setDataType(WechatHttpClient::DATA_TYPE_XML)
            ->setCertPemFile($this->certPemFile)
            ->setKeyPemFile($this->keyPemFile)
            ->post($api, $xml);
        return $this->getClientResult($res);
    }

    /**
     *
     * 统一下单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1</a>
     *
     * @param array $args ['body', 'out_trade_no', 'total_fee', 'notify_url', 'trade_type', 'openid']
     * @return array
     * @throws WechatException
     */
    public function unifiedOrder($args)
    {
        $args['spbill_create_ip'] = !empty($args['spbill_create_ip']) ? $args['spbill_create_ip'] : '127.0.0.1';
        $api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        return $this->send($api, $args);
    }

    /**
     *
     * 查询订单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2</a>
     *
     * @param array $args ['out_trade_no']
     * @return array
     * @throws WechatException
     */
    public function orderQuery($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/orderquery';
        return $this->send($api, $args);
    }

    /**
     *
     * 关闭订单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_3">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_3</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function closeOrder($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/closeorder';
        return $this->send($api, $args);
    }

    /**
     *
     * 申请退款, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_4">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_4</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function refund($args)
    {
        $args['appid'] = !empty($args['appid']) ? $args['appid'] : $this->appId;
        $args['mch_id'] = !empty($args['mch_id']) ? $args['mch_id'] : $this->mchId;
        $api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询退款, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_5">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_5</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function refundQuery($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/refundquery';
        return $this->send($api, $args);
    }

    /**
     *
     * 企业付款, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2</a>
     *
     * @param array $args ['partner_trade_no', 'openid', 'amount', 'desc']
     * @return array
     * @throws WechatException
     */
    public function transfers($args)
    {
        $args['mch_appid'] = !empty($args['mch_appid']) ? $args['mch_appid'] : $this->appId;
        $args['mchid'] = !empty($args['mchid']) ? $args['mchid'] : $this->mchId;
        $args['spbill_create_ip'] = !empty($args['spbill_create_ip']) ? $args['spbill_create_ip'] : '127.0.0.1';
        $args['check_name'] = !empty($args['check_name']) ? $args['check_name'] : 'NO_CHECK';
        $api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询企业付款, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3</a>
     *
     * @param array $args ['partner_trade_no']
     * @return array
     * @throws WechatException
     */
    public function getTransferInfo($args)
    {
        $args['appid'] = !empty($args['appid']) ? $args['appid'] : $this->appId;
        $args['mch_id'] = !empty($args['mch_id']) ? $args['mch_id'] : $this->mchId;
        $api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 企业付款到银行卡, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function payBank($args)
    {
        $args['mch_id'] = !empty($args['mch_id']) ? $args['mch_id'] : $this->mchId;
        $api = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询企业付款到银行卡, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function queryBank($args)
    {
        $args['mch_id'] = !empty($args['mch_id']) ? $args['mch_id'] : $this->mchId;
        $api = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';
        return $this->sendWithPem($api, $args);
    }

    /**
     * 通过数组数据验证签名
     * @param array $array
     * @return bool
     */
    public function validateSignByArrayResult($array)
    {
        if (!isset($array['sign'])) {
            return false;
        }
        $inputSign = $array['sign'];
        $truthSign = $this->makeSign($array);
        return $inputSign === $truthSign;
    }

    /**
     * 通过XML数据验证签名
     * @param string $xml
     * @return bool
     */
    public function validateSignByXmlResult($xml)
    {
        $array = WechatHelper::xmlToArray($xml);
        return $this->validateSignByArrayResult($array);
    }

    /**
     * 数据签名
     * @param array $args
     * @param string $signType
     * @return string
     */
    public function makeSign($args, $signType = self::SIGN_TYPE_MD5)
    {
        if (isset($args['sign'])) {
            unset($args['sign']);
        }
        ksort($args);
        $string = '';
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '') {
                continue;
            } else {
                $string .= ($i . '=' . $arg . '&');
            }
        }
        $string = $string . "key={$this->key}";
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
}
