<?php

namespace app\forms\mall\live;

use app\models\Model;
use GuzzleHttp\Client;

class CommonLive extends Model
{
    // 清理quota限制 每月10次机会，请勿滥用
    public function clearQuota($appid)
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
            $api = "https://api.weixin.qq.com/cgi-bin/clear_quota?access_token={$accessToken}";
            $res = $this->post($api, [
                'appid' => $appid,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            dd($res);
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    public static function post($url, $body = array())
    {
        $response = self::getClient()->post($url, [
            'json' => $body,
        ]);

        return $response;
    }

    public static function postFile($url, $body = array())
    {
        $response = self::getClient()->request('POST', $url, [
            'multipart' => $body,
        ]);

        return $response;
    }

    public static function get($url, $body = array())
    {
        $response = self::getClient()->get(self::buildParams($url, $body));

        return $response;
    }

    private static function buildParams($url, $array)
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

    private static function getClient()
    {
        return new Client([
            'verify' => false,
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public static function checkAccessToken()
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }

            return $accessToken;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
