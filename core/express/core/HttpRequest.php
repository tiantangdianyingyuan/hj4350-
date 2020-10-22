<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\core;

use GuzzleHttp\Client;

trait HttpRequest
{
    protected function get($url, $query = [], $headers = [])
    {
        return $this->request('GET', $url, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    protected function post($url, $params = [], $headers = [])
    {
        return $this->request('POST', $url, [
            'headers' => $headers,
            'form_params' => $params,
        ]);
    }

    public function request($method, $url, $options = [])
    {
        $client = $this->getClient($this->getBaseOptions());
        $response = $client->{$method}($url, $options);
        return $this->unwrapResponse($response);
    }

    //json
    protected function unwrapResponse($response)
    {
        $contents = $response->getBody()->getContents();
        return json_decode($contents, true);
    }

    protected function getClient($options = [])
    {
        return new Client($options);
    }

    protected function getBaseOptions()
    {
        return [
            'base_uri' => $this->getBaseUrl(),
            'timeout' => $this->getTimeout(),
            'verify' => false,
        ];
    }

    protected function getBaseUrl()
    {
        return '';
    }

    protected function getTimeout()
    {
        return '5.0';
    }
}
