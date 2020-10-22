<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\core;

use app\core\express\exception\HttpException;
use app\core\express\exception\WdException;
use GuzzleHttp\Client;

class Wd implements WdInterface
{
    private const HOST = 'https://wdexpress.market.alicloudapi.com';
    private const INQUIRE_PATH = '/gxali';
    private const LIST_PATH = '/globalExpressLists';
    private const METHOD = 'GET';

    private $head;

    public function __construct(array $config)
    {
        if (!isset($config['code']) || empty($config['code'])) {
            throw new WdException('APPCODE 不能为空');
        }
        $this->setHead($config['code']);
    }

    private function setHead($code)
    {
        $this->head = [
            'Authorization' => 'APPCODE ' . $code
        ];
    }

    public function getData(array $params)
    {
        return $this->client($this->head, $params, self::INQUIRE_PATH);
    }

    public function getList(array $params = [])
    {
        return $this->client($this->head, $params, self::LIST_PATH);
    }

    protected function client($headers, $params, $path)
    {
        $client = new Client(['verify' => false]);

        $response = $client->request(self::METHOD, self::HOST . $path, [
            'headers' => $headers,
            'query' => $params,
            'http_errors' => false,
        ]);

        $returnCode = $response->getStatusCode();
        if ($returnCode === self::HTTP_OK) {
            $body = $response->getBody();
            return json_decode($body, true);
        }
        if (isset(self::WD_INTERFACE_TEXT[$returnCode])) {
            throw new HttpException(self::WD_INTERFACE_TEXT[$returnCode]);
        }
        throw new HttpException('express_http_code undefined');
    }
}