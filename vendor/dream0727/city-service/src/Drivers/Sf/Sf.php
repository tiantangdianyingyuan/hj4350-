<?php

namespace CityService\Drivers\Sf;

use CityService\AbstractCityService;
use CityService\CityServiceInterface;
use CityService\Drivers\Sf\Response\SfResponse;
use CityService\Exceptions\HttpException;
use CityService\ResponseInterface;
use GuzzleHttp\Client;

class Sf extends AbstractCityService implements CityServiceInterface
{
    const BASE_URI = 'https://commit-openic.sf-express.com/open/api/external';
    //const BASE_URI = 'http://sfapi-proxy.jsonce.com/open/api/external';

    public function getAllImmeDelivery(): \CityService\ResponseInterface
    {
        // TODO: Implement getAllImmeDelivery() method.
    }

    /**
     * 预创建订单
     * http://commit-openic.sf-express.com/open/api/docs/index#/apidoc
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function preAddOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = 'precreateorder';

        $result = $this->post($path, $data);

        return new SfResponse(json_decode($result, true));
    }

    /**
     * 创建订单
     * http://commit-openic.sf-express.com/open/api/docs/index#/apidoc
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function addOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = 'createorder';

        $result = $this->post($path, $data);

        return new SfResponse(json_decode($result, true));
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
        $postData = json_encode($args);
        $signChar = $postData . "&{$this->getConfig('dev_id')}&{$this->getConfig('dev_key')}";
        $sign = base64_encode(MD5($signChar));
        return $sign;
    }

    private function post($path, array $data = [])
    {
        try {
            // 系统参数
            $data['dev_id'] = $this->getConfig('dev_id');
            $data['shop_id'] = $this->getConfig('shop_id');

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $url = self::BASE_URI . '/' . $path;

            return $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'sign' => $this->makeSign($data),
                ],
                'body' => json_encode($data),
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }
}
