<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 14:53
 */

namespace app\plugins\advance\events;

use app\plugins\advance\models\AdvanceGoods;
use yii\base\Event;

class GoodsEvent extends Event
{
    /** @var AdvanceGoods $advanceGoods */
    public $advanceGoods;
}