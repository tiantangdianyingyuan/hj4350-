<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\commands\express;

class wd extends Base
{
    public const FILE = 'wdExpress_list.json';
    public const MAP = [
        '亚马逊物流' => '亚马逊',
        '安能快运' => '安能物流',
        'COE东方快递' => 'COE快递',
        '跨越物流' => '跨越速运',
        '信丰物流' => '信丰快递',
        '邮政包裹信件' => '邮政快递包裹',
        '安能快递' => '安能物流',
        '中通快运' => '中通快递',
        '德邦快运' => '德邦',
        '京东快运' => '京东物流',
        '韵达快运' => '韵达速递',
        '邮政国内标快' => '邮政快递包裹',
        '秦远海运' => '',
        '特急送' => '',
        '全晨快递' => '',
        '快客快递' => '',
        '唐山申通' => '',
        '腾林物流' => '',
        '承诺达' => '',
        '丰恒物流' => '',
        '佳润达物流' => '',
    ];

    public function create()
    {
        $header = "Authorization: APPCODE 9fc80b985c154212b3b548de576891c2\r\n";
        $url = 'https://wdexpress.market.alicloudapi.com/globalExpressLists';
        $params = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $header
            ],
        ]);
        $file = file_get_contents($url, false, $params);
        $return = json_decode($file, true);
        if (isset($return['status']) && $return['status'] == 200) {
            $this->encrypt(self::FILE, array_flip($return['result']));
            $this->ok('[WdExpress生成成功][OK]');
        } else {
            $this->err('WdExpress接口请求失败');
        }
    }
}
