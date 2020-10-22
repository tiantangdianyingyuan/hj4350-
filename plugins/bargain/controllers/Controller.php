<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/9
 * Time: 17:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\controllers;



use app\plugins\bargain\Plugin;

class Controller extends \app\plugins\Controller
{
    public $sign;

    public function init()
    {
        parent::init();
        $this->sign = (new Plugin())->getName();
    }
}
