<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\mptemplate;

use app\models\Model;
use GuzzleHttp\Client;
use luweiss\Wechat\Wechat;

class MpTplMessage extends Model
{
    public function senderMsg($args = array(), $config)
    {
        if (!isset($args['touser']) || !$args['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }

        if (!isset($args['template_id']) || !$args['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的模板消息的id');
        }
        if (!isset($args['data']) || !$args['data']) {
            throw new \Exception('data字段缺失，请填写所需下发的模板消息的id');
        }


        $accessToken = $this->getWechat($config);
        $api = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$accessToken}";
        $res = $this->post($api, $args);

        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    private function getClient()
    {
        return new Client([
            'verify' => false,
        ]);
    }

    private function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);
        return json_decode($response->getBody(), true);
    }

    private function getWechat($config)
    {
        $wechat = new Wechat([
            'appId' => $config['app_id'],
            'appSecret' =>  $config['app_secret'],
        ]);
        return $wechat->getAccessToken();
    }
}
