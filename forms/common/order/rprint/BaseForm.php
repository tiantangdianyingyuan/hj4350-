<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order\rprint;

use app\models\Model;

abstract class BaseForm extends Model
{
    public $delivery;
    public $express;
    public $order;
    public $config;

    final public function baseDataSet($delivery, $order, $express, $config)
    {
        $this->delivery = $delivery;
        $this->order = $order;
        $this->express = $express;
        $this->config = $config;
    }

    final protected function getPrintAttributes()
    {
        $attributes = [];
        $class = new \ReflectionClass($this);
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes[$property->getName()] = $property->getValue($this);
        }
        return array_values($attributes);
        //get_object_vars($this) sign
    }

    abstract function track(...$params);

    protected function getGoodsName($goods, $delivery, &$goods_attr)
    {
        /** 规格名 **/
        $goods_info = \yii\helpers\BaseJson::decode($goods['goods_info']);
        $goods_attr_list = $goods_info['attr_list'];
        $attr_str = array_map(function ($item) {
            return $item['attr_group_name'] . ':' . $item['attr_name'] . ';';
        }, $goods_attr_list);

        $goods_attr = $goods_info['goods_attr'];

        $goodsName = $delivery['is_goods_alias'] == 1 ? $delivery['goods_alias'] ?: '商品' : $goods_attr['name'];
        $goodsName = $goodsName . '（' . trim(join($attr_str), ';') . '）';
        $text = substr($goodsName, 0, 100);
        $goodsName === $text || $goodsName = mb_substr($text, 0, mb_strlen($text) - 1);// 乱码
        return str_replace('+', '', $goodsName);
    }
}
