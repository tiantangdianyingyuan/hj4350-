<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/19
 * Time: 17:09
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\helpers;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use yii\base\Component;

class CurlHelper extends Component
{
    public static function getInstance()
    {
        return new self();
    }

    /**
     * @param array $headers
     * @return Client
     */
    public function getClient($headers = array())
    {
        return new Client([
            'verify' => false,
            'headers' => $headers
        ]);
    }

    public function httpPost($url, $params = [], $data = [])
    {
        try {
            $url = $this->appendParams($url, $params);
            $response = $this->getClient()->post($url, [
                'form_params' => $data,
            ]);
            $body = $response->getBody();
        } catch (ClientException $exception) {
            $body = $exception->getResponse()->getBody();
        }
        if (!$body) {
            throw new \Exception('请求没有有效的返回');
        }
        $res = json_decode($body->getContents(), true);
        if (!$res) {
            throw new \Exception('请求返回值为空');
        }
        return $res;
    }
    public function httpGet($url, $param = array(), $headers = array())
    {
        try {
            $url = $this->appendParams($url, $param);
            $client = $this->getClient($headers);
            $response = $client->get($url);
            $body = $response->getBody();
        } catch (ClientException $e) {
            $body = $e->getResponse()->getBody();
        }
        if (!$body) {
            throw new \Exception('请求没有有效的返回');
        }
        $res = json_decode($body->getContents(), true);
        if (!$res) {
            throw new \Exception('请求返回值为空');
        }
        return $res;
    }

    private function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = $this->paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    private function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            $v = urlencode($v);
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }
}
