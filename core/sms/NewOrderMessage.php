<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\core\sms;


use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;

class NewOrderMessage extends Message
{
    protected $order_no;
    protected $smsConfig;
    // protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    // protected $gateways = ['alidayu', 'yunpian', 'juhe']; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct($order_no, $smsConfig)
    {
        $this->order_no = $order_no;
        $this->smsConfig = $smsConfig;
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf('您有一条新的订单，订单号:', $this->order_no);
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        return $this->smsConfig['template_id'];
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        return [
            $this->smsConfig['template_variable'] => $this->order_no
        ];
    }
}
