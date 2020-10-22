<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common;

use app\models\Option;

class CommonOption
{
    private static $loadedOptions = [];

    /**
     * @param $name string Name
     * @param $value mixed Value
     * @param $mall_id integer Integer
     * @param $group string Name
     * @param $mch_id integer Name
     * @return boolean
     */
    public static function set($name, $value, $mall_id = 0, $group = '', $mch_id = 0)
    {
        if (empty($name)) {
            return false;
        }
        $model = Option::findOne([
            'name' => $name,
            'mall_id' => $mall_id,
            'group' => $group,
            'mch_id' => $mch_id,
        ]);
        if (!$model) {
            $model = new Option();
            $model->name = $name;
            $model->mall_id = $mall_id;
            $model->group = $group;
            $model->mch_id = $mch_id;
        }
        $model->value = \Yii::$app->serializer->encode($value);
        $result = $model->save();
        if ($result) {
            $loadedOptionKey = md5(json_encode([
                'name' => $name,
                'mall_id' => $mall_id,
                'group' => $group,
                'mch_id' => $mch_id,
            ]));
            self::$loadedOptions[$loadedOptionKey] = $value;
        }
        return $result;
    }

    /**
     * @param $name string Name
     * @param $mall_id integer Integer
     * @param $mch_id integer Integer
     * @param $group string Name
     * @param $default string Name
     * @return null
     */
    public static function get($name, $mall_id = 0, $group = '', $default = null, $mch_id = 0)
    {
        $loadedOptionKey = md5(json_encode([
            'name' => $name,
            'mall_id' => $mall_id,
            'group' => $group,
            'mch_id' => $mch_id,
        ]));
        if (array_key_exists($loadedOptionKey, self::$loadedOptions)) {
            return self::$loadedOptions[$loadedOptionKey];
        }
        $model = Option::findOne([
            'name' => $name,
            'mall_id' => $mall_id,
            'mch_id' => $mch_id,
            'group' => $group
        ]);

        if (!$model) {
            $result = $default;
        } else {
            $result = \Yii::$app->serializer->decode($model->value);
        }
        self::$loadedOptions[$loadedOptionKey] = $result;
        return $result;
    }

    /**
     * @param $list
     * @param int $mall_id
     * @param int $mch_id
     * @param string $group
     * @return bool
     */
    public static function setList($list, $mall_id = 0, $group = '', $mch_id = 0)
    {
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $item) {
            self::set(
                $item['name'],
                $item['value'],
                (isset($item['mall_id']) ? $item['mall_id'] : $mall_id),
                (isset($item['mch_id']) ? $item['mch_id'] : $mch_id),
                (isset($item['group']) ? $item['group'] : $group)
            );
        }
        return true;
    }

    /**
     * @param $names
     * @param int $mall_id
     * @param int $mch_id
     * @param string $group
     * @param null $default
     * @return array
     */
    public static function getList($names, $mall_id = 0, $group = '', $default = null, $mch_id = 0)
    {
        if (is_string($names)) {
            $names = explode(',', $names);
        }
        if (!is_array($names)) {
            return [];
        }
        $list = [];
        foreach ($names as $name) {
            if (empty($name)) {
                continue;
            }
            $value = self::get($name, $mall_id, $group, $default, $mch_id);
            $list[$name] = $value;
        }
        return $list;
    }
}
