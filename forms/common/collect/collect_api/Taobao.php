<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 14:04
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_api;


class Taobao extends CollectApi
{
    public function getData($itemId)
    {
        $url = 'https://api03.6bqb.com/taobao/detail?';
        $result = $this->httpGet($url . http_build_query(['apikey' => $this->api_key, 'itemid' => $itemId]));
        if (!$result || empty($result)) {
            throw new \Exception('淘宝--采集失败');
        }
        return $result;
    }
}
