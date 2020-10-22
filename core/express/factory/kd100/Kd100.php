<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory\kd100;

use app\core\express\core\Config;
use app\core\express\core\HttpRequest;
use app\core\express\exception\HttpException;
use app\core\express\exception\Kd100Exception;
use app\core\express\factory\ExpressExtends;
use app\core\express\factory\ExpressInterface;
use app\core\express\format\Kd100Format;
use app\core\express\Interfaces\Kd100ConfigurationConstant;
use app\validators\PhoneNumberValidator;
use yii\db\Exception;

class Kd100 extends ExpressExtends implements ExpressInterface, Kd100ConfigurationConstant
{
    use HttpRequest;

    public function track(...$params)
    {
        $model = $this->rInit(...$params);
        return $model->getExpressInfo();
    }

    private function handleParams($params)
    {
        list($express_no, $express_name, $phone) = $params;
        $this->express_no = $express_no;
        $this->express_code = $this->getExpressCode($params[1]);
        if ($express_name === '顺丰速运') {
            $pattern = (new PhoneNumberValidator())->pattern;
            if ($phone && !preg_match($pattern, $phone)) {
                throw new Kd100Exception('收件人手机号错误');
            }
        }
        $this->mobile = $phone;
        return $this;
    }

    private function rInit(...$params)
    {
        $return = $this->handleParams($params)->serverData();
        return (new Kd100Format())->injection($return);
    }

    private function serverData()
    {
        $param = json_encode([
            'com' => $this->express_code,
            'num' => $this->express_no,
            'phone' => $this->mobile,
        ]);
        $configModel = new Config();
        $config = $configModel->setFuncName(Kd100ConfigurationConstant::PROVIDER_NAME)->config($this->config);
        $params = [
            'customer' => $config['customer'],
            'sign' => strtoupper(md5($param . $config['code'] . $config['customer'])),
            'param' => $param,
        ];
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        try {
            $response = $this->post(Kd100ConfigurationConstant::SELECT_URL, $params, $header);
            if (isset($response['returnCode']) && $response['returnCode'] != 200) {
                throw new Exception($response['message']);
            }
            return $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage());
        }
    }

    protected function extraExpressCode()
    {
        try {
            $configModel = new Config();
            $config = $configModel->setFuncName(Kd100ConfigurationConstant::PROVIDER_NAME)->config($this->config);
            $params = [
                'num' => $this->express_no,
                'key' => $config['code'],
            ];
            $response = $this->get(Kd100ConfigurationConstant::LOGISTICS_COM_CODE_URL, $params, ['Content-Type' => 'application/x-www-form-urlencoded']);
            //todo 大概率返回key过期
            if (isset($response['returnCode']) && $response['returnCode'] != Kd100ConfigurationConstant::SUCCESS_STATUS) {
                throw new \Exception($response['message']);
            }
            return current($response)['comCode'];
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            return '';
        }
    }
}
