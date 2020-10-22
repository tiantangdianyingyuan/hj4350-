<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\handlers;


use yii\base\BaseObject;

abstract class HandlerBase extends BaseObject
{
    /**
     * 事件处理注册
     */
    abstract public function register();
}
