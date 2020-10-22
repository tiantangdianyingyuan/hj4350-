<?php

namespace CityService\Drivers\Dada;

use CityService\AbstractCityService;
use CityService\CityServiceInterface;
use CityService\Drivers\Dada\Response\DadaResponse;
use CityService\Exceptions\HttpException;
use CityService\ResponseInterface;
use GuzzleHttp\Client;

class Dada extends AbstractCityService implements CityServiceInterface
{
    const BASE_URI = 'https://newopen.imdada.cn';
    const TEST_URI = 'http://newopen.qa.imdada.cn';

    public function getAllImmeDelivery(): \CityService\ResponseInterface
    {
        // TODO: Implement getAllImmeDelivery() method.
    }

    /**
     * 预创建订单
     * https://newopen.imdada.cn/#/development/file/readyAdd?_k=qwld89
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function preAddOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = '/api/order/queryDeliverFee';

        $result = $this->post($path, $data);

        return new DadaResponse(json_decode($result, true));
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
        $path = '/api/order/addAfterQuery';

        $result = $this->post($path, $data);

        return new DadaResponse(json_decode($result, true));
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
            case 'accept':
                $path = '/api/order/accept';
                break;
            // 模拟取货
            case 'fetch':
                $path = '/api/order/fetch';
                break;
            // 模拟完成
            case 'finish':
                $path = '/api/order/finish';
                break;
            // 模拟取消
            case 'cancel':
                $path = '/api/order/cancel';
                break;
            // 模拟订单异常
            case 'back':
                $path = '/api/order/delivery/abnormal/back';
                break;
            default:
                throw new MtException('未知模拟类型');
                break;
        }

        $result = $this->post($path, $data);

        return new DadaResponse(json_decode($result, true));
    }

    /**
     * 签名验证
     * https://newopen.imdada.cn/#/quickStart/develop/safety?_k=s9qqt0
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function makeSign($data)
    {
        //1.升序排序
        ksort($data);

        //2.字符串拼接
        $args = "";
        foreach ($data as $key => $value) {
            $args .= $key . $value;
        }
        $args = $this->getConfig('appSecret') . $args . $this->getConfig('appSecret');
        //3.MD5签名,转为大写
        $sign = strtoupper(md5($args));

        return $sign;
    }

    /**
     * https://newopen.imdada.cn/#/quickStart/develop/mustRead?_k=dt6eiy
     * @param  [type] $path [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function post($path, array $data = [])
    {
        try {
            // 系统参数
            $newDada['app_key'] = $this->getConfig('appKey');
            $newDada['body'] = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : "";
            $newDada['format'] = 'json';
            $newDada['v'] = '1.0';
            $newDada['source_id'] = $this->getConfig('sourceId');
            $newDada['timestamp'] = time();
            $newDada['signature'] = $this->makeSign($newDada);

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $baseUrl = $this->getConfig('debug') ? self::TEST_URI : self::BASE_URI;
            $url = $baseUrl . $path;

            return $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($newDada, JSON_UNESCAPED_UNICODE),
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }

    /**
     * 获取城市信息
     * https://newopen.imdada.cn/#/development/file/cityList?_k=j5ml79
     * @return [type] [description]
     */
    public function getCityCodeList(): \CityService\ResponseInterface
    {
        $path = '/api/cityCode/list';

        $result = $this->post($path);

        return new DadaResponse(json_decode($result, true));
    }
}
