<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/7/30
 * Time: 10:47
 */

namespace app\plugins\bdapp;


use app\helpers\CurlHelper;
use app\models\Model;
use app\plugins\bdapp\forms\BdappPaymentForm;
use app\plugins\bdapp\forms\RsaSign;
use app\plugins\bdapp\forms\TemplateSendForm;
use app\plugins\bdapp\models\BdappConfig;
use app\plugins\bdapp\models\BdappTemplate;
use app\plugins\bdapp\models\BdTemplate;

class Plugin extends \app\plugins\Plugin
{

    public function getMenus()
    {
        return [
            [
                'name' => '基础配置',
                'route' => 'plugin/bdapp/index/setting',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '消息通知',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/bdapp/template-msg/setting',
            ],
            [
                'name' => '小程序发布',
                'route' => 'plugin/bdapp/index/package',
                'icon' => 'el-icon-setting',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/bdapp/index/setting';
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'bdapp';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '百度小程序';
    }

    public function getIsPlatformPlugin()
    {
        return true;
    }

    /**
     * @param string|array $param
     * @return array|\yii\db\ActiveRecord[]
     * 获取所有模板消息
     */
    public function getTemplateList($param = '*')
    {
        $list = BdappTemplate::find()->where(['mall_id' => \Yii::$app->mall->id])->select($param)->all();

        return $list;
    }

    /**
     * @return array
     */
    public function templateInfoList()
    {
        return [
            'order_pay_tpl' => [
                'id' => 'BD0221',
                'keyword_id_list' => [2, 9, 81, 34],
                'title' => '下单成功通知'
            ],
            'order_cancel_tpl' => [
                'id' => 'BD0021',
                'keyword_id_list' => [24, 5, 4, 17],
                'title' => '订单取消通知'
            ],
            'order_send_tpl' => [
                'id' => 'BD0003',
                'keyword_id_list' => [5, 2, 23, 55],
                'title' => '订单发货提醒'
            ],
            'order_refund_tpl' => [
                'id' => 'BD0022',
                'keyword_id_list' => [33, 13, 3, 4],
                'title' => '退款通知'
            ],
            'enroll_success_tpl' => [
                'id' => 'BD0261',
                'keyword_id_list' => [8, 9, 10],
                'title' => '信息提交成功通知'
            ],
            'enroll_error_tpl' => [
                'id' => 'BD0031',
                'keyword_id_list' => [6, 1, 7],
                'title' => '报名失败通知'
            ],
            'account_change_tpl' => [
                'id' => 'BD0643',
                'keyword_id_list' => [1, 3],
                'title' => '账户变动提醒'
            ],
            'audit_result_tpl' => [
                'id' => 'BD0141',
                'keyword_id_list' => [28, 1, 55, 5],
                'title' => '审核结果通知'
            ],
            'withdraw_success_tpl' => [
                'id' => 'BD0781',
                'keyword_id_list' => [5, 3, 6, 7],
                'title' => '提现到账通知'
            ],
            'withdraw_error_tpl' => [
                'id' => 'BD1161',
                'keyword_id_list' => [2, 3],
                'title' => '提现失败通知'
            ],
            'share_audit_tpl' => [
                'id' => 'BD0641',
                'keyword_id_list' => [1, 2, 6, 4],
                'title' => '审核状态通知'
            ],
            'remove_identity_tpl' => [
                'id' => 'BD0643',
                'keyword_id_list' => [5, 3],
                'title' => '账户变动提醒'
            ]
        ];
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
            $tpl = BdappTemplate::findOne(['mall_id' => \Yii::$app->mall->id, 'tpl_name' => $item['tpl_name']]);
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
                $tpl = new BdappTemplate();
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

    /**
     * @param $templateList
     * @return array
     * @throws \Exception
     * 微信小程序后台添加模板消息
     */
    public function addTemplate($templateList)
    {
        try {
            $template = new BdTemplate([
                'accessToken' => $this->getAccessToken()
            ]);
            $newList = [];
            // 已查询数量
            $count = 0;
            while (true) {
                $list = $template->getTemplateList($count, 20);
                $newList = array_merge($newList, $list['data']['list']);
                $count = count($newList);
                if (count($list['data']['list']) < 20) {
                    break;
                }
            }

            $templateIdList = [];
            foreach ($templateList as $index => $item) {
                $flag = true;
                foreach ($newList as $value) {
                    if ($item['title'] == $value['title']) {
                        $templateIdList[] = [
                            'tpl_name' => $index,
                            'tpl_id' => $value['template_id']
                        ];
                        $flag = false;
                        break;
                    }
                }
                if ($flag) {
                    try {
                        $res = $template->addTemplate($item['id'], $item['keyword_id_list']);
                        $templateIdList[] = [
                            'tpl_name' => $index,
                            'tpl_id' => $res['data']['template_id']
                        ];
                    } catch (\Exception $exception) {
                        continue;
                    }
                }
            }
            return $templateIdList;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @return \NuomiIntegrationCashierOrderConsumeRequest
     * @throws \Exception
     */
    public function getPaymentForm()
    {
        $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->pay_app_key || !$config->pay_private_key || !$config->pay_public_key || !$config->pay_dealid) {
            throw new \Exception('百度小程序支付信息尚未配置。');
        }
        $paymentForm = new BdappPaymentForm([
            'privateKeyFile' => $config->pay_private_key,
            'publicKeyFile' => $config->pay_public_key,
            'appKey' => $config->pay_app_key,
            'dealId' => $config->pay_dealid,
        ]);
        return $paymentForm;
    }

    public function checkSignWithRsa($requestParamsArr)
    {
        $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->pay_app_key || !$config->pay_private_key || !$config->pay_public_key || !$config->pay_dealid) {
            throw new \Exception('百度小程序支付信息尚未配置。');
        }
        $res = RsaSign::checkSignWithRsa($requestParamsArr, $config->pay_public_key);
        return $res;
    }

    public function genSignWithRsa($requestParamsArr)
    {
        $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->pay_app_key || !$config->pay_private_key || !$config->pay_public_key || !$config->pay_dealid) {
            throw new \Exception('百度小程序支付信息尚未配置。');
        }
        require __DIR__ . '/sdk/Autoloader.php';
        $res = RsaSign::genSignWithRsa($requestParamsArr, $config->pay_private_key);
        return $res;
    }

    /**
     * @return mixed
     * @throws \Exception
     * https://smartprogram.baidu.com/docs/develop/serverapi/power_exp/
     */
    public function getAccessToken()
    {
        $cacheKey = 'BAIDU_APP_ACCESS_TOKEN_' . \Yii::$app->mall->id;
        $cacheDuration = 86400;
        $accessToken = \Yii::$app->cache->get($cacheKey);
        if ($accessToken) {
            return $accessToken;
        }
        $api = 'https://openapi.baidu.com/oauth/2.0/token';
        $config = $this->getBdConfig();
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $config->app_key,
            'client_secret' => $config->app_secret,
            'scope' => 'smartapp_snsapi_base',
        ];
        $url = $api . "?" . http_build_query($params);
        $data = CurlHelper::getInstance()->httpPost($url);
        \Yii::$app->cache->set($cacheKey, $data['access_token'], $cacheDuration);
        return $data['access_token'];
    }

    public function getBdConfig()
    {
        $config = $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->app_key || !$config->app_secret) {
            throw new \Exception('小程序信息尚未配置。');
        }
        return $config;
    }

    /**
     * @param $id string 例如AT0009
     * @return mixed
     * @throws \Exception
     * 通过模板id获取模板的信息
     */
    public function idToInfo($id)
    {
        $template = new BdTemplate([
            'accessToken' => $this->getAccessToken()
        ]);
        $res = $template->getTemplateLibraryById($id);
        return $res;
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
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'home_nav' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'user_center' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
        ];
    }
}
