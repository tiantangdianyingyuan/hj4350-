<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/2
 * Time: 11:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall\cash;


use app\forms\mall\finance\BaseCashApply;
use app\plugins\community\forms\common\CommonMiddleman;

class CommunityCashApply extends BaseCashApply
{
    public function afterReject($cash)
    {
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($cash->user_id);
        $middleman->money += floatval($cash->price);
        if (!$middleman->save()) {
            throw new \Exception($this->getErrorMsg($middleman));
        }
        return true;
    }

    public function templatePath()
    {
        return 'plugins/community/cash-detail/cash-detail';
    }

    public function title()
    {
        return '社区团购提现';
    }

    public function desc($cash)
    {
        return '社区团购提现到余额，' . parent::desc($cash);
    }
}
