<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 14:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_api;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use yii\base\BaseObject;
use yii\helpers\Json;

abstract class CollectApi extends BaseObject
{
    public $api_key;
    public $itemId;

    abstract public function getData($itemId);

    public function httpGet($url, $param = array(), $headers = array())
    {
        try {
            $url = $this->appendParams($url, $param);
            $client = $this->getClient($headers);
            $response = $client->get($url);
            $body = $response->getBody();
            $data = Json::decode($body, true);
            if ($data['retcode'] !== '0000') {
                throw new \Exception($data['data']);
            }
        } catch (ClientException $e) {
            $body = $e->getResponse()->getBody();
        }
        return $body;
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
            throw new \Exception('x01');
        }
        $res = json_decode($body->getContents(), true);
        if (!$res) {
            throw new \Exception('x02');
        }
        return $res;
    }
}
