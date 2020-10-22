<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 15:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\printer;


use app\forms\common\prints\config\GpConfig;

class GpPrinter extends BasePrinter
{
    public function __construct($config = array())
    {
        $this->config = new GpConfig($config);
    }

    public function getTimes()
    {
        return "";
    }

    public function getCenter($content)
    {
        return "";
    }

    public function getBold($content)
    {
        return "";
    }

    public function getCenterBold($content)
    {
        return "";
    }

    public function getBR($content)
    {
        return "{$content}<gpBr/>";
    }

    public function getB($content){
        return "<gpWord Align=0 Bold=0 Wsize=2 Hsize=2 Reverse=0 Underline=0>收货人：{$content}</gpWord><gpBr/>";
    }

    public function getDB($content){
        return "<gpWord Align=0 Bold=0 Wsize=3 Hsize=3 Reverse=0 Underline=0>收货人：{$content}</gpWord><gpBr/>";
    }

    public function getTableNoAttr($data)
    {
        $content = '';
        $content .= "名称　　　　　 单价  数量   金额<gpBr/>";
        foreach ($data['goods_list'] as $k => $v) {
            $price = $v->unit_price;
            $arr = $this->rStrPad1($v->name, 7);
            foreach ($arr as $index => $value) {
                if ($index == 0) {
                    $content .= $value . " " . str_pad($price, 5) . " " . str_pad($v->num, 6) . " " . round($v->total_price, 2) . '<gpBr/>';
                } else {
                    $content .= $value . '<gpBr/>';
                }
            }

            if ($v->goods_no && $data['is_goods_no']) {
                $content .= $this->getBR('货号：' . $v->goods_no);
            }
        }
        return $content;
    }

    public function getTableAttr($data)
    {
        $content = '';
        $content .= "名称            数量    金额    <gpBr/>";
        foreach ($data['goods_list'] as $k => $v) {
            $name = $v->name . '（' . $v->attr . ')';
            $nameArr = $this->rStrPad1($name, 8);
            foreach ($nameArr as $index => $value) {
                if ($index == count($nameArr) - 1) {
                    $content .= $nameArr[$index] . " " . str_pad('×' . $v->num, 7) . " " . round($v->total_price, 2) . "<gpBr/>";
                } else {
                    $content .= $nameArr[$index] . '<gpBr/>';
                }
            }

            if ($v->goods_no && $data['is_goods_no']) {
                $content .= $this->getBR('货号：' . $v->goods_no);
            }
        }
        return $content;
    }
}
