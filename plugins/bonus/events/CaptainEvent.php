<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 17:16
 */

namespace app\plugins\bonus\events;

use app\plugins\bonus\models\BonusCaptain;
use yii\base\Event;

class CaptainEvent extends Event
{
    /** @var BonusCaptain $captain */
    public $captain;

    /**之前的队长**/
    public $parentId;
}