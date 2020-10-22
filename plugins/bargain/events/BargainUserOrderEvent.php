<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 14:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\events;


use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\BargainUserOrder;
use yii\base\Event;

/**
 * @property BargainUserOrder[] $bargainUserOrderAll
 * @property BargainOrder $bargainOrder
 */
class BargainUserOrderEvent extends Event
{
    public $bargainUserOrderAll;
    public $bargainOrder;
}
