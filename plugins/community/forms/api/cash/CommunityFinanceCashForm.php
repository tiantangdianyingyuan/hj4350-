<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/1
 * Time: 10:28
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\cash;


use app\forms\api\finance\BaseFinanceCashForm;
use app\forms\api\finance\UserInfo;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\Plugin;

/**
 * Class CommunityFinanceCashForm
 * @package app\plugins\community\forms\api\cash
 * @property CommunityMiddleman $middleman
 */
class CommunityFinanceCashForm extends BaseFinanceCashForm
{
    public $middleman;
    /**
     * 申请提现前插件各自的逻辑验证
     * @throws \Exception
     * @return mixed
     */
    protected function beforeCashValidate()
    {
        if ($this->setting['min_money'] > $this->price) {
            throw new \Exception('提现金额必须大于提现门槛金额（' . $this->setting['min_money'] . '）');
        }
        return true;
    }

    /**
     * 保存申请提现记录后
     * @return mixed
     * @throws \Exception
     */
    protected function afterSave()
    {
        $this->middleman->money -= $this->price;
        if (!$this->middleman->save()) {
            throw new \Exception($this->getErrorMsg($this->middleman));
        }
        return true;
    }

    /**
     * 设置一些额外的信息
     * 真实姓名和手机号
     * @param UserInfo $userInfo
     * @return UserInfo
     * @throws \Exception
     */
    protected function setUserInfo(UserInfo $userInfo)
    {
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($this->user->id);
        if (!$middleman || $middleman->status != 1) {
            throw new \Exception('不是团长不能申请提现');
        }
        if ($middleman->money < $this->price) {
            throw new \Exception('团长利润少于提现金额');
        }
        $userInfo->name = $middleman->name;
        $userInfo->phone = $middleman->mobile;
        $this->middleman = $middleman;
        return $userInfo;
    }

    /**
     * 返回一个插件标识
     * @return mixed
     */
    protected function setModel()
    {
        return (new Plugin())->getName();
    }

    public function getServiceCharge()
    {
        $setting = $this->setting;
        $serviceCharge = 0;
        if ($setting['free_cash_min'] > $this->price || $setting['free_cash_min'] === '') {
            $serviceCharge = price_format($setting['cash_service_charge']);
        }
        return $serviceCharge;
    }
}
