<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/3
 * Time: 3:48 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\helpers;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * @param $array
     * @param $keys
     * @param null $default
     * @return array|mixed|null
     * 批量删除键值
     */
    public static function removeList(&$array, $keys, $default = null)
    {
        if (!is_array($keys)) {
            return $default;
        }
        $value = [];
        while (count($keys) > 1) {
            $key = array_shift($keys);
            $value[] = self::remove($array, $key, $default);
        }
        return $value;
    }
}
