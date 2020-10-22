<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/8/2
 * Time: 9:37
 */

namespace app\plugins\ttapp;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\helpers\CurlHelper;
use app\models\Model;
use app\plugins\ttapp\forms\Decrypt;
use app\plugins\ttapp\forms\pay\TtPay;
use app\plugins\ttapp\forms\TemplateInfo;
use app\plugins\ttapp\forms\TemplateSendForm;
use app\plugins\ttapp\models\TtappConfig;
use app\plugins\ttapp\models\TtappTemplate;

class Plugin extends \app\plugins\Plugin
{
    private $xTtPay;

    public function getMenus()
    {
        return [
            [
                'name' => '基础配置',
                'route' => 'plugin/ttapp/index/setting',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/ttapp/template-msg/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '小程序发布',
                'route' => 'plugin/ttapp/index/package',
                'icon' => 'el-icon-setting',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/ttapp/index/setting';
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'ttapp';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '抖音/头条小程序';
    }

    public function getIsPlatformPlugin()
    {
        return true;
    }

    public function getTtPay()
    {
        if ($this->xTtPay) {
            return $this->xTtPay;
        }
        $ttappConfig = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$ttappConfig || !$ttappConfig || !$ttappConfig->alipay_public_key || !$ttappConfig->alipay_private_key || !$ttappConfig->alipay_app_id || !$ttappConfig->pay_app_secret || !$ttappConfig->pay_app_id) {
            throw new \Exception('头条小程序支付尚未配置。');
        }

        $config = [
            'appId' => $ttappConfig->pay_app_id,
            'secret' => $ttappConfig->pay_app_secret,
            'merchant_id' => $ttappConfig->mch_id,
            'alipay_app_id' => $ttappConfig->alipay_app_id,
            'alipay_public_key' => $ttappConfig->alipay_public_key,
            'alipay_private_key' => $ttappConfig->alipay_private_key,
        ];

        $this->xTtPay = new TtPay($config);
        return $this->xTtPay;
    }

    public function checkSign()
    {
        $config = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->alipay_public_key || !$config->alipay_private_key || !$config->alipay_app_id || !$config->pay_app_secret || !$config->pay_app_id) {
            throw new \Exception('头条小程序支付尚未配置。');
        }
        try {
            $passed = (new AopClient(
                $config->alipay_app_id,
                AlipayKeyPair::create($config->alipay_private_key, $config->alipay_public_key)
            ))->verify();
        } catch (\Exception $ex) {
            $passed = null;
            printf('%s | %s' . PHP_EOL, get_class($ex), $ex->getMessage()); // 验证过程发生错误，打印异常信息
            \Yii::error($ex->getMessage());
        }

        return $passed;
    }

    /**
     * 获取access_token
     * @return mixed
     * @throws \Exception
     * https://developer.toutiao.com/docs/server/auth/accessToken.html
     */
    public function getAccessToken()
    {
        $config = $this->getTtConfig();
        $cacheKey = 'TOUTIAO_APP_ACCESS_TOKEN_' . $config->app_key;
        $cacheDuration = 7000;
        $accessToken = \Yii::$app->cache->get($cacheKey);
        if ($accessToken) {
            return $accessToken;
        }
        $api = 'https://developer.toutiao.com/api/apps/token';
        $params = [
            'appid' => $config->app_key,
            'secret' => $config->app_secret,
            'grant_type' => 'client_credential',
        ];
        $url = $api . "?" . http_build_query($params);
        $data = CurlHelper::getInstance()->httpGet($url);
        \Yii::$app->cache->set($cacheKey, $data['access_token'], $cacheDuration);
        return $data['access_token'];
    }

    public function getTtConfig()
    {
        $config = $config = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config  || !$config->app_key || !$config->app_secret) {
            throw new \Exception('小程序信息尚未配置。');
        }
        return $config;
    }

    public function decryptData($data, $iv, $code)
    {
        $config = $config = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        $params = [
            'appid' => $config->app_key,
            'secret' => $config->app_secret,
            'code' => $code,
        ];
        $url = "https://developer.toutiao.com/api/apps/jscode2session";
        $url = $url . "?" . http_build_query($params);
        $session_key = CurlHelper::getInstance()->httpGet($url);
        if ($session_key['error'] != 0) {
            throw new \Exception('获取session_key失败');
        }
        $content = json_decode(Decrypt::decrypt($data, $iv, $session_key['session_key']), true);
        return $content;
    }

    /**
     * @param string|array $param
     * @return array|\yii\db\ActiveRecord[]
     * 获取所有模板消息
     */
    public function getTemplateList($param = '*')
    {
        $list = TtappTemplate::find()->where(['mall_id' => \Yii::$app->mall->id])->select($param)->all();

        return $list;
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws \Exception
     * 后台保存模板消息
     */
    public function addTemplateList($attributes)
    {
        foreach ($attributes as $item) {
            if (!isset($item['tpl_name'])) {
                throw new \Exception('缺少必要的参数tpl_name');
            }
            if (!isset($item[$item['tpl_name']])) {
                throw new \Exception("缺少必要的参数{$item['tpl_name']}");
            }
            $tpl = TtappTemplate::findOne(['mall_id' => \Yii::$app->mall->id, 'tpl_name' => $item['tpl_name']]);
            $tplId = $item[$item['tpl_name']];
            if ($tpl) {
                if ($tpl->tpl_id != $tplId) {
                    $tpl->tpl_id = $tplId;
                    if (!$tpl->save()) {
                        throw new \Exception((new Model())->getErrorMsg($tpl));
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            } else {
                $tpl = new TtappTemplate();
                $tpl->mall_id = \Yii::$app->mall->id;
                $tpl->tpl_name = $item['tpl_name'];
                $tpl->tpl_id = $tplId;
                if (!$tpl->save()) {
                    throw new \Exception((new Model())->getErrorMsg($tpl));
                } else {
                    continue;
                }
            }
        }
        return true;
    }

    public function templateSender()
    {
        return new TemplateSendForm();
    }

    public function getHeaderNav()
    {
        return [
            'name' => $this->getDisplayName(),
            'url' => \Yii::$app->urlManager->createUrl([$this->getIndexRoute()]),
            'new_window' => true,
        ];
    }

    public function getNotSupport()
    {
        return [
            'navbar' => [
                '/plugins/step/index/index',
                '/plugins/scratch/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/plugins/community/list/list',
                '/plugins/community/recruit/recruit',
                '/plugins/community/index/index',
            ],
            'home_nav' => [
                '/plugins/step/index/index',
                '/plugins/scratch/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/plugins/community/list/list',
                '/plugins/community/recruit/recruit',
                '/plugins/community/index/index',
            ],
            'user_center' => [
                '/plugins/step/index/index',
                '/plugins/scratch/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/plugins/community/list/list',
                '/plugins/community/recruit/recruit',
                '/plugins/community/index/index',
            ],
        ];
    }

    public function getTemplateData($type, $data)
    {
        return (new TemplateInfo($type, $data))->getData();
    }

    public function install()
    {
        $sql = <<<EOF
-- v1.0.5
CREATE TABLE `zjhj_bd_ttapp_jump_appid` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `appid` varchar(64) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
EOF;
        sql_execute($sql);
        return parent::install();
    }
}
