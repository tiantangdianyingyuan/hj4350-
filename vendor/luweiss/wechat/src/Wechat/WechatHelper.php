<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/5 12:01
 */


namespace luweiss\Wechat;


class WechatHelper
{
    /**
     * 数组转换成XML数据
     * @param array $array
     * @return string
     * @throws WechatException
     */
    public static function arrayToXml($array)
    {
        if (!is_array($array)) {
            throw new WechatException('`$arr`不是有效的array。');
        }
        $xml = "<xml>\r\n";
        $xml .= self::arrayToXmlSub($array);
        $xml .= "</xml>";
        return $xml;
    }

    private static function arrayToXmlSub($array)
    {
        if (!is_array($array)) {
            throw new WechatException('`$array`不是有效的array。');
        }
        $xml = "";
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if (is_numeric($key)) {
                    $xml .= self::arrayToXmlSub($val);
                } else {
                    $xml .= "<" . $key . ">" . self::arrayToXmlSub($val) . "</" . $key . ">\r\n";
                }
            } elseif (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">\r\n";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">\r\n";
            }
        }
        return $xml;
    }

    /**
     * XML数据转换成array数组
     * @param string $xml
     * @return array
     */
    public static function xmlToArray($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return (array)$res;
    }
}
