<?php
/**
 * ALIPAY API: alipay.fund.trans.toaccount.transfer request
 *
 * @author auto create
 *
 * @since  1.0, 2018-07-03 21:37:30
 */

namespace Alipay\Request;

class AlipayFundTransToaccountTransferRequest extends AbstractAlipayRequest
{
    /**
     * 单笔转账到支付宝账户接口
     **/
    private $bizContent;

    private $appCertSn;

    private $alipayRootCertSn;
    
    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParams['biz_content'] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    /**
     * @return mixed
     */
    public function getAppCertSn()
    {
        return $this->appCertSn;
    }

    /**
     * @param mixed $appCertSn
     */
    public function setAppCertSn($appCertSn): void
    {
        $this->appCertSn = $appCertSn;
        $this->apiParams['app_cert_sn'] = $appCertSn;
    }

    /**
     * @return mixed
     */
    public function getAlipayRootCertSn()
    {
        return $this->alipayRootCertSn;
    }

    /**
     * @param mixed $alipayRootCertSn
     */
    public function setAlipayRootCertSn($alipayRootCertSn): void
    {
        $this->alipayRootCertSn = $alipayRootCertSn;
        $this->apiParams['alipay_root_cert_sn'] = $alipayRootCertSn;
    }
}
