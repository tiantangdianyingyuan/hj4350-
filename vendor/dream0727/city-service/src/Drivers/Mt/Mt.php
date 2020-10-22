<?php

namespace CityService\Drivers\Mt;

use CityService\AbstractCityService;
use CityService\CityServiceInterface;
use CityService\Drivers\Mt\Exceptions\MtException;
use CityService\Drivers\Mt\Response\MtResponse;
use CityService\Exceptions\HttpException;
use CityService\ResponseInterface;
use GuzzleHttp\Client;

class Mt extends AbstractCityService implements CityServiceInterface
{
    const BASE_URI = 'https://peisongopen.meituan.com/api';

    public function getAllImmeDelivery(): \CityService\ResponseInterface
    {
        // TODO: Implement getAllImmeDelivery() method.
    }

    /**
     * 预创建订单
     * https://peisong.meituan.com/open/doc#section2-1
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function preAddOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = '/order/createByShop';

        $result = $this->post($path, $data);

        return new MtResponse(json_decode($result, true));
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
        $path = '/order/createByShop';

        $result = $this->post($path, $data);

        return new MtResponse(json_decode($result, true));
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
        $path = '/order/delete';

        $result = $this->post($path, $data);

        return new MtResponse(json_decode($result, true));
    }

    public function abnormalConfirm(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement abnormalConfirm() method.
    }

    public function getOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement getOrder() method.
    }

    /**
     * 模拟配送测试
     * https://peisong.meituan.com/open/doc#section3-2
     * @param  [type] $mockType [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public function mockUpdateOrder(array $data = [], array $params = []): \CityService\ResponseInterface
    {
        if (!isset($params['mock_type'])) {
            throw new MtException('mock_type异常');
        }

        switch ($params['mock_type']) {
            // 模拟接单
            case 'arrange':
                $path = '/test/order/arrange';
                break;
            // 模拟取货
            case 'pickup':
                $path = '/test/order/pickup';
                break;
            // 模拟送达
            case 'deliver':
                $path = '/test/order/deliver';
                break;
            // 模拟改派
            case 'rearrange':
                $path = '/test/order/rearrange';
                break;
            // 模拟上传异常
            case 'reportException':
                $path = '/test/order/reportException';
                break;
            default:
                throw new MtException('未知模拟类型');
                break;
        }

        $result = $this->post($path, $data);

        return new MtResponse(json_decode($result, true));
    }

    /**
     * 签名验证
     * https://peisong.meituan.com/open/doc#section1-3
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function makeSign($data)
    {
        ksort($data);

        $args = $this->getConfig('appSecret');

        foreach ($data as $key => $value) {
            if ($value) {
                $args .= $key . $value;
            }
        }

        $sign = sha1($args);

        return $sign;
    }

    /**
     * https://peisong.meituan.com/open/doc#section1-1
     * @param  [type] $path [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function post($path, array $data = [])
    {
        try {
            // 系统参数
            $data['appkey'] = $this->getConfig('appKey');
            $data['shop_id'] = $this->getConfig('shopId');
            $data['timestamp'] = time();
            $data['version'] = '1.0';
            $data['sign'] = $this->makeSign($data);

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $url = self::BASE_URI . $path;

            return $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => $data,
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }
}
