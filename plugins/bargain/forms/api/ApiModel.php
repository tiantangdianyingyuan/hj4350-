<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/18
 * Time: 14:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\models\Mall;
use app\models\Model;
use app\models\User;

/**
 * @property Mall $mall
 * @property User $user
 */
class ApiModel extends Model
{
    protected $mall;
    protected $user;

    public function setMall($val)
    {
        $this->mall = $val;
    }

    public function setUser($val)
    {
        $this->user = $val;
    }
}
