<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 16:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\content;

use app\forms\common\prints\Exceptions\PrintException;

/**
 * Class BaseContent
 * @package app\forms\common\prints\content
 * @property string $attribute
 */
class BaseContent
{
    public function __construct($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            throw new PrintException(get_class($this) . '不存在属性：' . $name);
        }
    }

    public function setAttribute($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }
}
