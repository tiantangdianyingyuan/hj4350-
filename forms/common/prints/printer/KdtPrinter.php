<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 10:37
 */

namespace app\forms\common\prints\printer;

// 365打印机
use app\forms\common\prints\config\KdtConfig;

class KdtPrinter extends BasePrinter
{
    public function __construct($config = array())
    {
        $this->config = new KdtConfig($config);
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
        return "<CB>{$content}</CB><BR>";
    }

    public function getBR($content)
    {
        return "{$content}<BR>";
    }

    public function getB($content){
        return "<B>{$content}</B><BR>";
    }

    public function getDB($content){
        return "<DB>{$content}</DB><BR>";
    }

    public function getTableNoAttr($data)
    {
        $content = '';
        $content .= "名称　　　　　 单价  数量   金额<BR>";
        foreach ($data['goods_list'] as $k => $v) {
            $price = $v->unit_price;
            $arr = $this->rStrPad1($v->name, 7);
            foreach ($arr as $index => $value) {
                if ($index == 0) {
                    $content .= $value . " " . str_pad($price, 5) . " " . str_pad($v->num, 6) . " " . round($v->total_price, 2) . '<BR>';
                } else {
                    $content .= $value . '<BR>';
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
        $content .= "名称            数量    金额    <BR>";
        foreach ($data['goods_list'] as $k => $v) {
            $name = $v->name . '（' . $v->attr . ')';
            $nameArr = $this->rStrPad1($name, 8);
            foreach ($nameArr as $index => $value) {
                if ($index == count($nameArr) - 1) {
                    $content .= $nameArr[$index] . " " . str_pad('×' . $v->num, 7) . " " . round($v->total_price, 2) . "<BR>";
                } else {
                    $content .= $nameArr[$index] . '<BR>';
                }
            }

            if ($v->goods_no && $data['is_goods_no']) {
                $content .= $this->getBR('货号：' . $v->goods_no);
            }
        }
        return $content;
    }
}
