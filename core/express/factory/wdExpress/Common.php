<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory\wdExpress;

use app\core\express\exception\WdException;
use app\models\Express;
use app\validators\PhoneNumberValidator;

trait Common
{
    //code 相同情况下
    public static function tDiff(string $express)
    {
        $company = Express::getOne($express);
        if ($company) {
            switch ($company['code']) {
                case 'JDKY':
                    return 'JD';
                case 'DBLKY':
                    return 'DBL';
                case 'JTSD':
                    return 'JITU';
                default:
                    return $company['code'];
            }
        }
        return self::tDiffTwo($express);
    }

    // name 相同情况下
    public static function tDiffTwo(string $express_name)
    {
        $diffArr = [];
        $express_name = $diffArr[$express_name] ?? $express_name;
        $fileModel = new Company();
        $express_list = $fileModel->getList();
        $key = array_search($express_name, $express_list);
        if ($key === false) {
            throw new WdException('无法找到此快递，注意适配');
        }
        return $key;
    }

    public static function nDiff($sto, $order_no, $phone = '')
    {
        if ($sto === 'SF') {
            $pattern = (new PhoneNumberValidator())->pattern;
            if ($phone && !preg_match($pattern, $phone)) {
                throw new WdException('收件人手机号错误');
            }
            return $order_no . ':' . substr($phone, -4);
        }
        return $order_no;
    }
}