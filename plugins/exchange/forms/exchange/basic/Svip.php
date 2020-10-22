<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;


use app\forms\common\vip_card\CommonVipSend;
use app\plugins\exchange\models\ExchangeSvipOrder;

class Svip extends BaseAbstract implements Base
{
    public function exchange(&$message)
    {
        try {
            //$remark = sprintf('使用兑换码%s', $this->codeModel->code);
            $return = CommonVipSend::getInstance()->becomeOrRenew($this->config['child_id'], $this->user->id, 'exchange');
            if ($return['code'] === 0) {
                $order_id = $return['data']['order_id'];
                $this->createPluginOrder($order_id);
                return true;
            } else {
                throw new \Exception($return['msg']);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }

    public function createPluginOrder($order_id)
    {
        $order = new ExchangeSvipOrder();
        $order->mall_id = $this->user->mall_id;
        $order->user_id = $this->user->id;
        $order->order_id = $order_id;
        $order->code_id = $this->codeModel->id;
        $order->save();
    }
}