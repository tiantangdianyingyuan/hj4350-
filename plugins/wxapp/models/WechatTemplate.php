<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/19
 * Time: 15:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models;


use app\models\Model;
use GuzzleHttp\Client;

class WechatTemplate extends Model
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
     * @param $id integer 模板标题id，可通过接口获取，也可登录小程序后台查看获取 例如AT0002
     * @param $keywordList array 开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如[3,5,4]或[4,5,3]），最多支持10个关键词组合
     * @return array
     * @throws \Exception
     * 组合模板并添加至帐号下的个人模板库
     * https://developers.weixin.qq.com/miniprogram/dev/api/addTemplate.html
     */
    public function addTemplate($id, $keywordList)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'id' => $id,
            'keyword_id_list' => $keywordList
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $templateId string 要删除的模板id 例如wDYzYZVxobJivW9oMpSCpuvACOfJXQIoKUm0PY397Tc
     * @return array
     * @throws \Exception
     * 删除帐号下的某个模板
     * https://developers.weixin.qq.com/miniprogram/dev/api/deleteTemplate.html
     */
    public function deleteTemplate($templateId)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'template_id' => $templateId,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $id String 模板标题 id 例如AT0002
     * @return array
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/api/getTemplateLibraryById.html
     */
    public function getTemplateLibraryById($id)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'id' => $id,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $offset integer 用于分页，表示从offset开始。从 0 开始计数。
     * @param $count integer 用于分页，表示拉取count条记录。最大为 20。
     * @return array
     * @throws \Exception
     * 获取小程序模板库标题列表
     * https://developers.weixin.qq.com/miniprogram/dev/api/getTemplateLibraryList.html
     */
    public function getTemplateLibraryList($offset, $count)
    {
        if ($count >= 20) {
            $count = 20;
        }
        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'offset' => $offset,
            'count' => $count,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $offset integer 用于分页，表示从offset开始。从 0 开始计数。
     * @param $count integer 用于分页，表示拉取count条记录。最大为 20。
     * @return array
     * @throws \Exception
     * 获取帐号下已存在的模板列表
     * https://developers.weixin.qq.com/miniprogram/dev/api/getTemplateList.html
     */
    public function getTemplateList($offset, $count)
    {
        if ($count >= 20) {
            $count = 20;
        }
        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'offset' => $offset,
            'count' => $count,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $arg array
     * @return array
     * @throws \Exception
     * 发送订阅消息
     * https://developers.weixin.qq.com/miniprogram/dev/api/sendTemplateMessage.html
     */
    public function sendTemplateMessage($arg = array())
    {
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的订阅消息的id');
        }
        if (!isset($arg['form_id']) || !$arg['form_id']) {
            throw new \Exception('form_id字段缺失，请填写接收者（用户）可用的form_id');
        }
        $api = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$this->accessToken}";
        $res = $this->post($api, $arg);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }
}
