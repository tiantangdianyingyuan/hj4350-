<?php

namespace app\forms\common;

class CommonOptionP
{
    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    public function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = self::check($list[$key], $value);
            }
        }
        return $list;
    }

    public function saveEnd(array $default)
    {
        foreach ($default as $k => $i) {
            foreach ($i as $k1 => $i1) {
                if (in_array($k1, ['width', 'height', 'size', 'top', 'left'])) {
                    $default[$k][$k1] = (float)$default[$k][$k1] / 2;
                }
            }
        }
        return $default;
    }

    public function poster($list, $default = [])
    {
        $new_list = $this->check($list, $default);
        $check = ['width', 'height', 'size', 'top', 'left'];
        $checkArr = ['size', 'top', 'left', 'width', 'height', 'font', 'is_show', 'type'];
        // 将个别字段转为INT类型
        foreach ($new_list as $k => $posterItem) {
            foreach ($posterItem as $checkItemKey => $checkItem) {
                if (in_array($checkItemKey, $checkArr)) {
                    $new_list[$k][$checkItemKey] = (float)$posterItem[$checkItemKey];
                }
                if (in_array($checkItemKey, $check)) {
                    $new_list[$k][$checkItemKey] = $new_list[$k][$checkItemKey] * 2;
                }
            }
        }
        return $new_list;
    }
}