<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 15:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\events;


use app\models\Mall;
use app\plugins\fxhb\models\FxhbUserActivity;
use yii\base\Event;

/**
 * @property FxhbUserActivity $userActivity
 * @property FxhbUserActivity $parentActivity
 * @property Mall $mall
 */
class JoinActivityEvent extends Event
{
    public $userActivity;
    public $parentActivity;
    public $mall;
}
