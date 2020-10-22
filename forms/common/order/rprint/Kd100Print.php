<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order\rprint;

use app\core\express\core\HttpRequest;
use app\models\Express;
use app\models\Order;
use app\models\OrderExpressSingle;

class Kd100Print extends BaseForm
{
    use HttpRequest;


    public function track(...$params)
    {
        $params = func_get_arg(0);
        list($delivery, $express, $order, $config) = $this->getPrintAttributes();

        $query = [];
        $delivery['customer_account'] && $query['payType'] = 'THIRDPARTY';
        $delivery['customer_account'] && $query['partnerId'] = $delivery['customer_account'];
        $delivery['customer_pwd'] && $query['partnerKey'] = $delivery['customer_pwd'];
        $query['type'] = 10;
        $query['payType'] = 'SHIPPER';
        $query['net'] = $delivery['outlets_name'];
        $query['kuaidicom'] = $this->getCode($express['name']);
        foreach ($order->detail as $v) {
            if (!empty($params['order_detail_ids']) && !in_array($v['id'], $params['order_detail_ids'])) {
                //排除订单
                continue;
            }
            $query["cargo"] = $this->getGoodsName($v, $delivery, $goods_attr);
            $query["count"] = (int)$v->num;
            $query["weight"] = floor($goods_attr['weight']) / 1000;
            break;
        }

        $query['expType'] = $delivery['kd100_business_type'] ?: '标准快递';
        $query['tempid'] = $delivery['kd100_template'];
        $query['remark'] = '';
        $query['siid'] = $config['kd100_siid'];//重赋
        $query['sendMan'] = $this->selectSendMan($delivery);
        $query['recMan'] = $this->selectRecMan($order);

        //todo 未测试
        $result = $this->serverRequest($query, $config);
        if (isset($result['result']) && $result['result'] === true && $result['status'] == 200) {
            file_put_contents('kd100test.log.json', json_encode($result, JSON_UNESCAPED_UNICODE));
            $form = new OrderExpressSingle();
            $form->mall_id = \Yii::$app->mall->id;
            $form->order_id = $order->id;
            //$form->ebusiness_id = $result['taskId'];
            $form->ebusiness_id = $config['kd100_key'];
            $form->order = \Yii::$app->serializer->encode($result);
            $form->print_teplate = $result['data'][0]['template'][0] ?: '';
            $form->is_delete = 0;
            $form->express_code = $express['code'];
            if (!$form->save()) {
                throw new \Exception($this->getErrorMsg($form));
            }
            return array_merge($result, ['express_single' => $form]);
        } else {
            throw new \DomainException(\yii\helpers\BaseJson::encode($result));
        }
    }

    private function serverRequest($param, $config)
    {
        $body = [];
        list($msec, $sec) = explode(' ', microtime());
        $body['t'] = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $body['key'] = $config['kd100_key'];
        $body['method'] = 'eOrder';
        $requestUrl = 'https://poll.kuaidi100.com/printapi/printtask.do';
        //$this->test($requestUrl, $body, $param);//普通打印
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);
        $body['param'] = $param;
        $body['sign'] = strtoupper(md5($body['param'] . $body['t'] . $body['key'] . $config['kd100_secret']));
        return $this->get($requestUrl, $body, ['Content-Type' => 'application/x-www-form-urlencoded']);
    }

    private function test(&$requestUrl, &$body, &$param)
    {
        $param['needTemplate'] = 1;
        $requestUrl = 'https://poll.kuaidi100.com/eorderapi.do';
        $body['method'] = 'getElecOrder';
    }

    private function selectSendMan($delivery)
    {
        $province_city_district = $delivery['province'] . $delivery['city'] . $delivery['district'];
        return [
            'name' => $delivery['name'],
            'mobile' => $delivery['mobile'] ?: $delivery['tel'],
            'printAddr' => $province_city_district . $delivery['address'],
            'company' => $delivery['company'],
        ];
    }

    private function selectRecMan(Order $order)
    {
        $address_data = explode(' ', $order->address, 4);
        $province_city_district = current($address_data) . next($address_data) . next($address_data);
        return [
            'name' => $order->name,
            'mobile' => $order->mobile,
            'printAddr' => $province_city_district . str_replace(PHP_EOL, '', $address_data[3] ?: $order->address),
            'company' => '',
        ];
    }

    protected function getCode($express_company, $select_type = 'kd100')
    {
        $express_code = Express::getExpress();
        $express_code = array_column($express_code, 'alias', 'name');
        return $express_code[$express_company][$select_type];
    }
}
