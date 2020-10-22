<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/19
 * Time: 9:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models;


use app\models\Model;
use GuzzleHttp\Client;

class WechatSubscribe extends Model
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
            'body' => json_encode($body),
        ]);
        return json_decode($response->getBody(), true);
    }

    public function postForm($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'form_params' => $body
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
     * @param $tid integer 模板标题 id，可通过接口获取，也可登录小程序后台查看获取 例如AT0002
     * @param $kidList array 开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param $sceneDesc string 服务场景描述，15个字以内
     * @return array
     * @throws \Exception
     * 组合模板并添加至帐号下的个人模板库
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.addTemplate.html
     */
    public function addTemplate($tid, $kidList, $sceneDesc)
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$this->accessToken}";
        $res = $this->postForm($api, [
            'tid' => $tid,
            'kidList' => $kidList,
            'sceneDesc' => $sceneDesc
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $priTmplId string 要删除的模板id 例如wDYzYZVxobJivW9oMpSCpuvACOfJXQIoKUm0PY397Tc
     * @return array
     * @throws \Exception
     * 删除帐号下的某个模板
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.deleteTemplate.html
     */
    public function deleteTemplate($priTmplId)
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token={$this->accessToken}";
        $res = $this->postForm($api, [
            'priTmplId' => $priTmplId,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @return array
     * @throws \Exception
     * 获取小程序账号的类目
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getCategory.html
     */
    public function getCategory()
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getcategory?access_token={$this->accessToken}";
        $res = $this->get($api);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $tid string 模板标题 id，可通过接口获取
     * @return array
     * @throws \Exception
     * 获取模板标题下的关键词列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateKeyWordsById.html
     */
    public function getPubTemplateKeyWordsById($tid)
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatekeywords?access_token={$this->accessToken}";
        $res = $this->get($api, [
            'tid' => $tid
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $ids string 类目 id，多个用逗号隔开
     * @param $start number 用于分页，表示从 start 开始。从 0 开始计数。
     * @param $limit number 用于分页，表示拉取 limit 条记录。最大为 30。
     * @return array
     * @throws \Exception
     * 获取帐号所属类目下的公共模板标题
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateTitleList.html
     */
    public function getPubTemplateTitleList($ids, $start, $limit)
    {
        if ($limit >= 30) {
            $limit = 30;
        }
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatetitles?access_token={$this->accessToken}";
        $res = $this->get($api, [
            'ids' => $ids,
            'start' => $start,
            'limit' => $limit,
        ]);
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @return array
     * @throws \Exception
     * 获取当前帐号下的个人模板列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getTemplateList.html
     */
    public function getTemplateList()
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$this->accessToken}";
        $res = $this->get($api);
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
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.send.html
     */
    public function send($arg = array())
    {
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的订阅消息的id');
        }
        $api = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$this->accessToken}";
        $res = $this->post($api, $arg);
        \Yii::error($res['errmsg']);
        \Yii::error($arg);
        switch ($res['errcode']) {
            case 0:
                return $res;
            case 40003:
                throw new \Exception('用户为空或者不正确');
                break;
            case 40037:
                throw new \Exception('订阅模板id不正确');
                break;
            case 43101:
                throw new \Exception('用户拒绝接受消息，如果用户之前曾经订阅过，则表示用户取消了订阅关系');
                break;
            case 47003:
                throw new \Exception('模板参数不准确，可能为空或者不满足规则，errmsg会提示具体是哪个字段出错' . $res['errmsg']);
                break;
            case 41030:
                throw new \Exception('跳转路径不正确，需要保证在线上版本小程序中存在');
                break;
            default:
                throw new \Exception($res['errmsg']);
        }
    }
}
