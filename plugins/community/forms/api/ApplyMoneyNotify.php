<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 11:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;

class ApplyMoneyNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        try {
            $setting = CommonSetting::getCommon()->getSetting();
            $common = CommonMiddleman::getCommon();
            $middleman = $common->getConfigByToken($paymentOrder->orderNo);
            if (!$middleman) {
                throw new \Exception('用户未申请成为社区团购团长');
            }
            if ($setting['is_apply'] == 1) {
                $middleman->status = 0;
            } else {
                $middleman->status = 1;
                $middleman->become_at = mysql_timestamp();
                $middleman->reason = '无需审核';
            }
            switch ($paymentOrder->payType) {
                case PaymentOrder::PAY_TYPE_BALANCE:
                    $middleman->pay_type = 3;
                    break;
                case PaymentOrder::PAY_TYPE_WECHAT:
                    $middleman->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_ALIPAY:
                    $middleman->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_TOUTIAO:
                    $middleman->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_BAIDU:
                    $middleman->pay_type = 1;
                    break;
                default:
                    break;
            }
            $middleman->save();
            return true;
        } catch (\Exception $exception) {
            \Yii::error($exception);
            throw $exception;
        }
    }
}
