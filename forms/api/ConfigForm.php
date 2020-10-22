<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/15 15:09
 */


namespace app\forms\api;


use app\core\exceptions\ClassNotFoundException;
use app\forms\api\app_platform\Transform;
use app\forms\common\AppImg;
use app\forms\common\CommonAppConfig;
use app\forms\common\config\UserCenterConfig;
use app\forms\common\share\CommonShareConfig;
use app\forms\mall\recharge\RechargePageForm;
use app\forms\mall\share\ShareCustomForm;
use app\models\Model;
use app\plugins\diy\Plugin;

class ConfigForm extends Model
{
    public function search()
    {
        $mall = \Yii::$app->mall->getMallSetting();
        $mall['setting']['web_service_url'] = urlencode($mall['setting']['web_service_url']);
        $mall['setting']['latitude_longitude'] = $mall['setting']['latitude'] . ',' . $mall['setting']['longitude'];

        $plugin = $this->getPluginConfig();
        $barTitle = CommonAppConfig::getBarTitle();

        $navbar = CommonAppConfig::getNavbar();
        $navbar = Transform::getInstance()->transformNavbar($navbar);

        $res = [
            'code' => 0,
            'data' => [
                'mall' => $mall,
                'navbar' => $navbar,
                'user_center' => UserCenterConfig::getInstance()->getApiUserCenter(),
                'plugin' => $plugin,
                'copyright' => CommonAppConfig::getCoryRight(),
                '__wxapp_img' => AppImg::search(),
                'share_setting' => CommonShareConfig::config(),
                'share_setting_custom' => (new ShareCustomForm())->getData()['data'],
                'recharge_page_custom' => (new RechargePageForm())->getSetting(),
                'auth_page' => $this->getDefaultAuthPage(),
                'cat_style' => CommonAppConfig::getAppCatStyle(),
                'bar_title' => $barTitle,
            ],
        ];

        return $res;
    }

    private function getPluginConfig()
    {
        $data = [];
        $list = \Yii::$app->plugin->getList();
        foreach ($list as $item) {
            try {
                $data[$item->name] = \Yii::$app->plugin->getPlugin($item->name)->getAppConfig();
            } catch (ClassNotFoundException $exception) {
            }
        }
        return $data;
    }

    public function getDefaultAuthPage()
    {
        try {
            /* @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            $result = $plugin->getAlonePage('auth');
        } catch (ClassNotFoundException $exception) {
            $pages = CommonAppConfig::getDefaultPageList();
            $result = $pages['auth'];
        }
        return $result;
    }
}
