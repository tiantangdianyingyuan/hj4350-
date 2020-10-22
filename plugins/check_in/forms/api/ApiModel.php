<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/27
 * Time: 9:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\api;


use app\models\User;
use app\plugins\check_in\forms\Model;

/**
 * @property User $user
 */
class ApiModel extends Model
{
    protected $user;

    public function setUser($val)
    {
        $this->user = $val;
    }
}
