<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:18
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\events;


use app\plugins\bargain\models\BargainGoods;
use yii\base\Event;

/**
 * @property BargainGoods $bargainGoods
 */
class BargainGoodsEvent extends Event
{
    public $bargainGoods;
}
