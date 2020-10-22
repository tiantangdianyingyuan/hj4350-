<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory\wdExpress;

use app\core\express\core\Wd;
use app\core\express\exception\WdException;
use app\core\express\factory\ExpressInterface;
use app\core\express\format\WdFormat;

class WdExpress implements ExpressInterface
{
    use Common;

    public $n; //快递单号
    public $t; //快递物流公司类型
    public $p; //电话号码

    public $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function validate($params)
    {
        if (empty($params[0]) || empty($params[1])) {
            throw new WdException('订单号或快递公司不能为空');
        }
    }

    /**
     * @param mixed ...$params 订单号 快递公司 手机号(收件人)
     * @return mixed
     * @throws \Exception
     */
    public function track(...$params)
    {
        $this->validate($params);
        $this->t = common::tDiff($params[1]);
        $this->n = common::nDiff($this->t, $params[0], $params[2]);

        $serverData = $this->serverData();
        if ($serverData['State'] === '-1') {
            throw new WdException($serverData['Reason']);
        }
        return (new WdFormat())->getData($serverData);
    }

    public function serverData()
    {
        $server = new Wd($this->config);
        $params = ['n' => $this->n, 't' => $this->t];
        return $server->getData($params);
    }
}
