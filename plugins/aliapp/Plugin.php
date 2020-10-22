<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/13 11:58
 */


namespace app\plugins\aliapp;


use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\models\AliappConfig;
use app\models\Model;
use app\plugins\aliapp\forms\TemplateInfo;
use app\plugins\aliapp\forms\TemplateSendForm;
use app\plugins\aliapp\models\AliappTemplate;

class Plugin extends \app\plugins\Plugin
{

    public function getMenus()
    {
        return [
            [
                'name' => '基础配置',
                'route' => 'plugin/aliapp/index/setting',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/aliapp/template-msg/setting',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '小程序发布',
                'route' => 'plugin/aliapp/index/package',
                'icon' => 'el-icon-setting',
            ],
        ];
    }

    public function handler()
    {
        // TODO: Implement handler() method.
    }

    public function getIndexRoute()
    {
        return 'plugin/aliapp/index/setting';
    }

    /**
     * @return AopClient
     * @throws \Exception
     */
    public function getAliAopClient()
    {
        $aliappConfig = $this->getAliConfig();
        $aop = new AopClient(
            $aliappConfig->appid,
            AlipayKeyPair::create($aliappConfig->app_private_key, $aliappConfig->alipay_public_key)
        );
        return $aop;
    }

    public function getAliConfig()
    {
        $aliappConfig = AliappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$aliappConfig) {
            throw new \Exception('支付宝小程序支付尚未配置。');
        }
        return $aliappConfig;
    }

    /**
     * 插件唯一id，与插件文件夹一致，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'aliapp';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '支付宝小程序';
    }

    public function getIsPlatformPlugin()
    {
        return true;
    }

    public function getHeaderNav()
    {
        return [
            'name' => $this->getDisplayName(),
            'url' => \Yii::$app->urlManager->createUrl(['plugin/aliapp/index/setting']),
            'new_window' => true,
        ];
    }

    public function templateSender()
    {
        return new TemplateSendForm();
    }

    public function checkSign()
    {
        $config = $this->getAliConfig();
        if (!$config || !$config->alipay_public_key || !$config->app_private_key || !$config->appid) {
            throw new \Exception('支付宝小程序支付尚未配置。');
        }
        try {
            $passed = $this->getAliAopClient()->verify();
        } catch (\Exception $ex) {
            $passed = null;
            printf('%s | %s' . PHP_EOL, get_class($ex), $ex->getMessage()); // 验证过程发生错误，打印异常信息
            \Yii::error($ex->getMessage());
        }

        return $passed;
    }

    /**
     * @param string|array $param
     * @return array|\yii\db\ActiveRecord[]
     * 获取所有模板消息
     */
    public function getTemplateList($param = '*')
    {
        $list = AliappTemplate::find()->where(['mall_id' => \Yii::$app->mall->id])->select($param)->all();

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
            $tpl = AliappTemplate::findOne(['mall_id' => \Yii::$app->mall->id, 'tpl_name' => $item['tpl_name']]);
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
                $tpl = new AliappTemplate();
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

    public function getTemplateData($type, $data)
    {
        return (new TemplateInfo($type, $data))->getData();
    }

    /**
     * @return array
     * 不支持的功能
     */
    public function getNotSupport()
    {
        return [
            'navbar' => [
                'contact',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'home_nav' => [
                'contact',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'user_center' => [
                'contact',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
        ];
    }
}
