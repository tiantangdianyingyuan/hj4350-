<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/30
 * Time: 11:40
 */

namespace app\plugins\advance\events;

use app\plugins\advance\models\AdvanceOrder;
use yii\base\Event;

class DepositEvent extends Event
{
    /** @var AdvanceOrder $advanceOrder */
    public $advanceOrder;
}