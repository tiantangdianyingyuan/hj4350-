<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 17:06
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms;


use app\models\Mall;

/**
 * @property Mall $mall
 */
class Model extends \app\models\Model
{
    protected $mall;

    public function setMall($val)
    {
        $this->mall = $val;
    }
}
