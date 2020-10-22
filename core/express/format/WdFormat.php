<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\format;

/**
 * Class Format 返回值格式化
 * @package app\core\express\wdExpress
 */
class WdFormat
{
    public function getData(array $data): array
    {
        return [
            'status' => $data['State'],
            'status_text' => $this->getCode($data['State']),
            'list' => $this->getTrack($data['Traces']),
        ];
    }

    public function getTrack(array $data): array
    {
        return array_map(function ($item) {
            return [
                'desc' => $item['AcceptStation'],
                'datetime' => $item['AcceptTime'],
                'memo' => '',
            ];
        }, $data);
    }

    public function getCode(int $code): string
    {
        $arr = [
            -1 => '单号或快递公司代码错误',
            0 => '暂无轨迹',
            1 => '快递收件',
            2 => '在途中',
            3 => '签收',
            4 => '问题件',
            5 => '疑难件',
            6 => '退件签收',
            7 => '快递收件(揽件)',
        ];
        return $arr[$code] ?? '错误码未定义';
    }
}