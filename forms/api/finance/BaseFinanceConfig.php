<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/1
 * Time: 15:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\finance;


use app\models\Model;

abstract class BaseFinanceConfig extends Model
{
    /**
     * @throws \Exception
     * @return array
     */
    abstract public function config();
}
