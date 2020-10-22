<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/22
 * Time: 16:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\gift\handlers;


use app\handlers\orderHandler\OrderCreatedHandlerClass;

class OrderCreatedHandler extends OrderCreatedHandlerClass
{

    protected function setShareMoney()
    {
        return $this;
    }


}
