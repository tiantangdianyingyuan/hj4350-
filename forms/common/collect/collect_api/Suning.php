<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-08-06
 * Time: 11:56
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_api;


class Suning extends CollectApi
{
    public $shopid;

    public function getData($itemId)
    {
        $url = 'https://api03.6bqb.com/suning/detail?';
        $result = $this->httpGet($url . http_build_query(['apikey' => $this->api_key, 'itemid' => $itemId, 'shopid' => $this->shopid]));
        if (!$result || empty($result)) {
            throw new \Exception('苏宁易购--采集失败');
        }
        return $result;
    }
}