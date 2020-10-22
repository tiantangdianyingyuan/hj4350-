<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/24 9:24
 */


namespace app\events;


use app\models\User;
use yii\base\Event;

class UserEvent extends Event
{
    /** @var User $user */
    public $user;
}
