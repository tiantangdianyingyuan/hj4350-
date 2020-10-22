<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 16:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common\continue_type;


use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;

/**
 * @property Common $common;
 */
abstract class BaseState extends Model
{
    public $common;

    abstract public function setJob();

    abstract public function clearContinue();
}
