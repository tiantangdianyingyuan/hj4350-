<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/22
 * Time: 15:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\events;


use app\models\Share;
use yii\base\Event;

/**
 * @property Share $share
 */
class ShareEvent extends Event
{
    public $share;
}
