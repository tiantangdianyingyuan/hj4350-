<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/28
 * Time: 18:30
 */

namespace app\core\newsms;


use app\forms\common\CommonOption;
use app\models\Option;
use yii\base\Component;

class Sms extends Component
{
    const MODULE_ADMIN = 'admin';
    const MODULE_MALL = 'mall';

    private $moduleSmsList = [];

    /**
     * @param string $module Sms::MODULE_ADMIN 或 Sms::MODULE_MALL
     * @return ModuleSms
     * @throws \Exception
     */
    public function module($module)
    {
        if (isset($this->moduleSmsList[$module])) {
            return $this->moduleSmsList[$module];
        }
        switch ($module) {
            case static::MODULE_ADMIN:
                $indSetting = CommonOption::get(Option::NAME_IND_SETTING);
                if ($indSetting
                    && isset($indSetting['ind_sms'])
                    && isset($indSetting['ind_sms']['aliyun'])) {
                    $config = $indSetting['ind_sms']['aliyun'];
                    $moduleSms = new ModuleSms([
                        'gateways' => [
                            'aliyun' => [
                                'access_key_id' => isset($config['access_key_id']) ? $config['access_key_id'] : '',
                                'access_key_secret' => isset($config['access_key_secret']) ?
                                    $config['access_key_secret'] : '',
                                'sign_name' => isset($config['sign']) ? $config['sign'] : '',
                            ],
                        ],
                    ]);
                } else {
                    throw new \Exception('短信信息尚未配置。');
                }
                $this->moduleSmsList[$module] = $moduleSms;
                break;
            case static::MODULE_MALL:
                $option = CommonOption::get(Option::NAME_SMS, \Yii::$app->mall->id, Option::GROUP_ADMIN);
                if ($option) {
                    $moduleSms = new ModuleSms([
                        'gateways' => [
                            'aliyun' => [
                                'access_key_id' => isset($option['access_key_id']) ? $option['access_key_id'] : '',
                                'access_key_secret' => isset($option['access_key_secret']) ?
                                    $option['access_key_secret'] : '',
                                'sign_name' => isset($option['template_name']) ? $option['template_name'] : '',
                            ],
                        ],
                    ]);
                } else {
                    throw new \Exception('短信信息尚未配置。');
                }
                $this->moduleSmsList[$module] = $moduleSms;
                break;
            default:
                throw new \Exception('尚未支持的module: ' . $module);
                break;
        }
        return $this->moduleSmsList[$module];
    }
}
