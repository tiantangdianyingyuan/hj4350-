<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 17:16
 */

namespace app\plugins\stock\events;

use app\plugins\stock\models\StockUser;
use yii\base\Event;

class StockEvent extends Event
{
    /** @var StockUser $stock */
    public $stock;
}
