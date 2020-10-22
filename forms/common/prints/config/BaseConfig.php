<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/19
 * Time: 19:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\config;

use app\forms\common\prints\Exceptions\PrintException;

abstract class BaseConfig
{
    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param string $content
     * @return array
     * @throws PrintException
     */
    abstract public function print($content);
}
