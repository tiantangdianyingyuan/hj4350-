<?php

namespace CityService\Drivers\Ss;

use CityService\AbstractCityService;
use CityService\CityServiceInterface;
use CityService\Drivers\Ss\Response\SsResponse;
use CityService\Exceptions\HttpException;
use CityService\ResponseInterface;
use GuzzleHttp\Client;

/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/8/7
 * Time: 14:15
 */
class Ss extends AbstractCityService implements CityServiceInterface
{
    const BASE_URI_DEBUG = 'http://open.s.bingex.com/openapi/merchants/v5'; //开发环境
    const BASE_URI = 'http://open.ishansong.com/openapi/merchants/v5'; //正式环境

    public function getAllImmeDelivery(): \CityService\ResponseInterface
    {
        // TODO: Implement getAllImmeDelivery() method.
    }

    /**
     * 预创建订单
     * http://open.ishansong.com/documentCenter
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function preAddOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = 'orderCalculate';

        $result = $this->post($path, $data);

        return new SsResponse(json_decode($result, true));
    }

    /**
     * 创建订单
     * http://open.ishansong.com/documentCenter
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function addOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = 'orderPlace';

        $result = $this->post($path, $data);

        return new SsResponse(json_decode($result, true));
    }

    public function reOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement reOrder() method.
    }

    public function addTip(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement addTip() method.
    }

    public function preCancelOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement preCancelOrder() method.
    }

    public function cancelOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement cancelOrder() method.
    }

    public function abnormalConfirm(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement abnormalConfirm() method.
    }

    public function getOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement getOrder() method.
    }

    public function mockUpdateOrder(array $data = [], array $params = []): \CityService\ResponseInterface
    {
        // TODO: Implement mockUpdateOrder() method.
    }

    private function makeSign($args)
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
                $string .= ($i . $arg);
            }
        }
        $string = $this->getConfig('secret') . $string;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }

    private function post($path, array $data = [])
    {
        try {

            $data['clientId'] = $this->getConfig('clientId');
            $data['shopId'] = $this->getConfig('shopId');
            $data['timestamp'] = $this->getMillisecond();
            $data['sign'] = $this->makeSign($data);

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $baseUrl = $this->getConfig('debug') ? self::BASE_URI_DEBUG : self::BASE_URI;
            $url = $baseUrl . '/' . $path;

            return $client->post($url, [
                'form_params' => $data,
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }

    private function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}
