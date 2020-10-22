<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/19
 * Time: 19:38
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\config;


use app\forms\common\prints\Exceptions\PrintException;

class GpConfig extends BaseConfig
{
    public $memberCode;
    public $deviceNo;
    public $apiKey;
    public $time;
    public $orderNo;
    private $url = 'http://api.poscom.cn/apisc/sendMsg';

    public function print($content)
    {
        header("Content-Type: text/html;charset=utf-8");
        $reqTime = $this->getMillisecond();
        $securityCode = md5($this->memberCode . $this->deviceNo . $this->orderNo . $reqTime . $this->apiKey);
        $result = $this->requestPost($this->url, [
            'charset' => 'UTF-8',
            'reqTime' => $reqTime,
            'memberCode' => $this->memberCode,
            'deviceNo' => $this->deviceNo,
            'securityCode' => $securityCode,
            'msgDetail' => $content,
            'msgNo' => $this->orderNo,
            'mode' => 2,
            'times' => $this->time,
            'reprint' => 1
        ]);
        $result = json_decode($result, true);
        if ($result['code'] != 0) {
            throw new PrintException($result['msg'], $result, 1);
        }
        return $result;
    }

    private function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    private function requestPost($url = '', $post_data = array())
    {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
}
