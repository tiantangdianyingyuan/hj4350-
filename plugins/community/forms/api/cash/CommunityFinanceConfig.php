<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/1
 * Time: 15:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\cash;


use app\core\response\ApiCode;
use app\forms\api\finance\BaseFinanceConfig;
use app\forms\common\template\TemplateList;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;

class CommunityFinanceConfig extends BaseFinanceConfig
{
    public function config()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig(\Yii::$app->user->id);
        if (!$middleman || $middleman->status != 1) {
            throw new \Exception('当前用户不是团长');
        }
        $tpl = ['withdraw_error_tpl', 'withdraw_success_tpl'];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'config' => [
                    'pay_type' => $setting['pay_type'],
                    'min_money' => $setting['min_money'],
                    'cash_service_charge' => $setting['cash_service_charge'],
                    'free_cash_min' => $setting['free_cash_min'],
                    'money' => $middleman->money,
                ],
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $tpl),
            ],
        ];
    }
}
