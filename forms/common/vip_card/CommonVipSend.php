<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/8/20
 * Time: 15:59
 */

namespace app\forms\common\vip_card;

class CommonVipSend extends CommonVip
{
    /**
     * 新增或者续费超级会员卡用户
     * @param $detail_id int 子卡id
     * @param $user_id int 用户id
     * @param string $sign
     * @param int $payType 支付类型
     * @param int $isRecordOrder
     * @return
     * @throws \Exception
     */
    public function becomeOrRenew($detail_id, $user_id, $sign = '', $payType = 1, $isRecordOrder = 1)
    {
        if ($this->plugin !== false && $this->permission !== false) {
            return $this->plugin->becomeOrRenew($detail_id, $user_id, $sign, $payType, $isRecordOrder);
        } else {
            throw new \Exception('无超级会员卡权限');
        }
    }
}
