<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\format;

abstract class BaseFormat
{
    public $attributes;

    public const F_STATE = 'state';
    public const F_STSTUS = 'status';
    public const F_STSTUS_TEXT = 'status_text';
    public const F_STSTUS_LIST = 'list';
    public const T_ITEM_DESC = 'desc';
    public const T_ITEM_DATETIME = 'datetime';
    public const T_ITEM_MEMO = 'memo';

    //todo 禁止实例化？
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    public function __get($property)
    {
        return $this->getAttribute($property);
    }

    abstract public function injection(array $arr);

    public function getExpressInfo()
    {
        $arr = $this->attributes;
        return [
            self::F_STATE => $arr[self::F_STATE],
            self::F_STSTUS => $arr[self::F_STSTUS],
            self::F_STSTUS_TEXT => $arr[self::F_STSTUS_TEXT],
            self::F_STSTUS_LIST => $arr[self::F_STSTUS_LIST],
        ];
    }
}
