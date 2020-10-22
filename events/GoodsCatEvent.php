<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/11
 * Time: 10:09
 */

namespace app\events;

use app\models\GoodsCats;
use yii\base\Event;

class GoodsCatEvent extends Event
{
    /** @var GoodsCats */
    public $cats;

    public $catsList;

    public $isVipCardCats;
}
