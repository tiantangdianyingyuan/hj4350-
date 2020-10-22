<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/19
 * Time: 10:32
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_api;


class Alibaba extends CollectApi
{
    public function getData($itemId)
    {
        $url = 'https://api03.6bqb.com/alibaba/detail?';
        $result = $this->httpGet($url . http_build_query(['apikey' => $this->api_key, 'itemid' => $itemId]));
        if (!$result || empty($result)) {
            throw new \Exception('1688阿里巴巴--采集失败');
        }
        return $result;
    }
}
