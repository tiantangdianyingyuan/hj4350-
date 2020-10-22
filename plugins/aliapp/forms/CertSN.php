<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/12
 * Time: 13:41
 */

namespace app\plugins\aliapp\forms;

class CertSN
{
    public static function getSn($str, $isroot = false)
    {
        if ($isroot) {
            return self::getRootCertSN($str);
        } else {
            return self::getCertSN($str);
        }
        return $md5_str;
    }

    public static function getRootCertSN($str)
    {
        // return '687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6';
        $arr = preg_split('/(?=-----BEGIN)/', $str, -1, PREG_SPLIT_NO_EMPTY);
        $str = null;
        foreach ($arr as $e) {
            $sn = self::getCertSN($e, true);
            if (!$sn) {
                continue;
            }
            if ($str === null) {
                $str = $sn;
            } else {
                $str .= "_" . $sn;
            }
        }
        return $str;
    }

    public static function getCertSN($str, $matchAlgo = false)
    {
        /*
        根据java SDK源码：AntCertificationUtil::getRootCertSN
        对证书链中RSA的项目进行过滤（猜测是gm国密算法java抛错搞不定，故意略去）
        java源码为：

        if(c.getSigAlgOID().startsWith("1.2.840.113549.1.1"))

        根据 https://www.alvestrand.no/objectid/1.2.840.113549.1.1.html
        该OID为RSA算法系。
         */
        if ($matchAlgo) {
            openssl_x509_export($str, $out, false);
            if (!preg_match('/Signature Algorithm:.*?RSA/im', $out, $m)) {
                return;
            }

        }
        $a = openssl_x509_parse($str);
        $issuer = null;
        // 注意：根据java代码输出，需要倒着排列 CN,OU,O
        foreach ($a["issuer"] as $k => $v) {
            if ($issuer === null) {
                $issuer = "$k=$v";
            } else {
                $issuer = "$k=$v," . $issuer;
            }
        }
        #    echo($issuer . $a["serialNumber"] . "\n");
        $serialNumberHex = self::decimalNotation($a['serialNumberHex']);
        $sn = md5($issuer . $serialNumberHex);
        return $sn;
    }

    public static function decimalNotation($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }
}