<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 15:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\printer;


use app\forms\common\prints\config\YilianyunConfig;

class YilianyunPrinter extends BasePrinter
{
    public function __construct($config = array())
    {
        $this->config = new YilianyunConfig($config);
    }

    public function getTimes()
    {
        return "<MN>{$this->config->time}</MN>";
    }

    public function getCenter($content)
    {
        return "<center>{$content}</center>";
    }

    public function getBold($content)
    {
        return "<FB><FS2>{$content}</FS2></FB>";
    }

    public function getCenterBold($content)
    {
        return "<FB><center>{$content}</center></FB>\n";
    }

    public function getBR($content)
    {
        return "{$content}\n";
    }

    public function getB($content){
        return "<FS>{$content}</FS>\n";
    }

    public function getDB($content){
        return "<FS2>{$content}</FS2>\n";
    }

    public function getTableNoAttr($data)
    {
        $content = '';
        $content .= "<table><tr><td>名称</td><td>数量</td><td>单价</td></tr>";
        foreach ($data['goods_list'] as $k => $v) {
            $price = $v->unit_price;
            $v->name = str_replace('，', ',', $v->name);
            $arr = $this->rStrPad1($v->name, 7);
            foreach ($arr as $index => $value) {
                if ($index == 0) {
                    $content .= "<tr><td>" . $value . "</td><td>" . $v->num . "</td><td>" . $price . "</td></tr>";
                } else {
                    $content .= "<tr><td>" . $value . "</td></tr>";
                }
            }

            if ($v->goods_no && $data['is_goods_no']) {
                $content .= "<tr><td>货号：" . $v->goods_no . "</td></tr>";
            }
        }
        $content .= "</table>";
        return $content;
    }

    public function getTableAttr($data)
    {
        $content = '';
        $content .= "<table><tr><td>名称</td><td>数量</td><td>总价</td></tr>";
        foreach ($data['goods_list'] as $k => $v) {
            $name = $v->name . '（' . $v->attr . ')';
            $nameArr = $this->rStrPad1($name, 6);
            foreach ($nameArr as $index => $value) {
                if ($index == count($nameArr) - 1) {
                    $content .= "<tr><td>" . $nameArr[$index] . "</td><td>" . '×' . $v->num . "</td><td>" . round($v->total_price, 2) . "</td></tr>";
                } else {
                    $content .= "<tr><td>" . $nameArr[$index] . "</td><td></td><td></td></tr>";
                }
            }

            if ($v->goods_no && $data['is_goods_no']) {
                $content .= "<tr><td>货号：" . $v->goods_no . "</td><td></td><td></td></tr>";
            }
        }
        $content .= "</table>";
        return $content;
    }
}
