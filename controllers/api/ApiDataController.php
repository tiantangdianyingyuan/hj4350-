<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\api;


use app\core\response\ApiCode;
use app\forms\mall\order\OrderSendForm;
use app\models\Express;

class ApiDataController extends ApiController
{
    private function exportScript($data): array
    {
        if (!is_array($data)) {
            throw new \Exception('e1');
        }

        foreach ($data as $item) {
            static $k;
            if ($item[0]) {
                $k = $item[0];
            }
            $arr[$k][] = [
                'name' => $item[1],
                'value' => $item[2]
            ];
        }

        $newArr = [];
        $express = Express::getExpressList();
        $keys = array_column($express, 'code', 'name');
        foreach ($arr as $k => $i) {
            if (in_array($k, ['京东快递', '苏宁物流', '丹鸟物流', '东方汇'])) {
                continue;
            }
            //补充
            if (in_array($k, ['远成快运'])) {
                continue;
            }
            $k === '德邦快递' && $k = '德邦';
            $k === '龙邦快运' && $k = '龙邦快递';
            $k1 = $keys[$k];
            $newArr[$k1] = $i;
        }
        return $newArr;
    }

    public function actionTest()
    {

    }

    public function updateBusiness()
    {
        $url = 'aHR0cHM6Ly93d3cua2RuaWFvLmNvbS9maWxlL+W/q+mAkuWFrOWPuOW/q+mAkuS4muWKoeexu+Weiy54bHN4';
        try {
            $send = new OrderSendForm();
            $url = base64_decode($url);
            $temp = $send->up($url);
            $data = $send->getExcel($temp);
            $newArr = $this->exportScript($data);
            $file = 'statics/text/business.json';
            file_put_contents($file, json_encode($newArr));
            @unlink($temp);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function testBusiness()
    {
        return Express::getBusiness();
    }
}