<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/12
 * Time: 10:35
 */

namespace app\plugins\ttapp\models;


use app\models\Model;
use GuzzleHttp\Client;

class TtTemplate extends Model
{
    public $accessToken;

    public function getClient()
    {
        return new Client([
            'verify' => false,
        ]);
    }

    public function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);
        return json_decode($response->getBody(), true);
    }

    public function get($url, $params = array())
    {
        $response = $this->getClient()->get($this->buildParams($url, $params));
        return json_decode($response->getBody(), true);
    }

    public function buildParams($url, $array)
    {
        $query = http_build_query($array);
        $url = trim($url, '?');
        $url = trim($url, '&');
        if (mb_stripos($url, '?')) {
            return $url . '&' . $query;
        } else {
            return $url . '?' . $query;
        }
    }


    /**
     * @param $arg array
     * @return array
     * @throws \Exception
     * 发送模板消息
     * https://developer.toutiao.com/docs/server/template_message/send.html
     */
    public function sendTemplateMessage($arg = array())
    {
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的模板消息的id');
        }
        if (!isset($arg['form_id']) || !$arg['form_id']) {
            throw new \Exception('form_id字段缺失，请填写接收者（用户）可用的form_id');
        }
        $arg['access_token'] = $this->accessToken;
        $api = "https://developer.toutiao.com/api/apps/game/template/send";
        \Yii::info($arg);
        $res = $this->post($api, $arg);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            \Yii::error('头条小程序发送模板消息失败');
            \Yii::error($res);
            throw new \Exception($res['errmsg']);
        }
    }
}
