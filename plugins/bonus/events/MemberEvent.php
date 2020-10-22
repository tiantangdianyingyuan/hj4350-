<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/30
 * Time: 17:15
 */

namespace app\plugins\bonus\events;

use app\plugins\bonus\models\BonusCaptain;
use yii\base\Event;

class MemberEvent extends Event
{
    /** @var BonusCaptain $captain */
    public $captain;
}