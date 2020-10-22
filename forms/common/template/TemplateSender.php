<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/20
 * Time: 9:32
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template;


use app\models\Model;

abstract class TemplateSender extends Model
{
    public $is_need_form_id = true;
    abstract public function sendTemplate($arg = array());
}
