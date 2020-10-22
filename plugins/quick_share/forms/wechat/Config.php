<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\wechat;


use app\forms\common\CommonOption;
use app\models\Option;
use luweiss\Wechat\Wechat;

trait Config
{
    protected $app_id;
    protected $app_secret;
    protected $access_token;

    /**
     * @return $this
     * @throws \Exception
     */
    public function getOption()
    {
        $option = CommonOption::get(Option::NAME_WX_PLATFORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) throw new \Exception('公众号配置不能为空');

        $this->app_id = $option->app_id;
        $this->app_secret = $option->app_secret;
        return $this;
    }

    /**
     * @param string $app_id
     * @param string $app_secret
     * @return $this
     * @throws \luweiss\Wechat\WechatException
     */
    public function getAccountToken($app_id = '', $app_secret = '')
    {
        empty($app_id) && $app_id = $this->app_id;
        empty($app_secret) && $app_secret = $this->app_secret;

        if (empty($app_id)) {
            throw new \Exception('appid 不能为空');
        }
        if (empty($app_secret)) {
            throw new \Exception('app_secret 不能为空');
        }

        $wechat = new Wechat([
            'appId' => $this->app_id,
            'appSecret' => $this->app_secret,
        ]);
        $this->access_token = $wechat->getAccessToken();
        return $this;
    }

    /**
     * @param string $app_id
     * @param string $access_token
     * @return bool|mixed
     * @throws \Exception
     */
    public function getJsTicket($app_id = '', $access_token = '')
    {
        empty($app_id) && $app_id = $this->app_id;
        empty($access_token) && $access_token = $this->access_token;

        $key = 'WECHAT_JSAPI_TICKET_' . $app_id;

        $ticket = \Yii::$app->cache->get($key);
        if ($ticket) return $ticket;

        $api = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $result = Tools::httpGet($api);

        if ($result) {
            $json = \yii\helpers\Json::decode($result);
            if (empty($json) || !empty($json['errcode'])) {
                $errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                throw new \Exception($errCode . $errMsg);
            }
            $ticket = $json['ticket'];
            \Yii::$app->cache->set($key, $ticket, $json['expires_in'] ? intval($json['expires_in']) - 100 : 3600);
            return $ticket;
        }
        return false;
    }


}