<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 17:01
 */


namespace app\events;


use app\models\Order;
use yii\base\Event;

class CartEvent extends Event
{
    /** @var Order */
    public $cartIds;
}
