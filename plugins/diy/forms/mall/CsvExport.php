<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\mall;


class CsvExport extends \app\core\CsvExport
{
    public function handleRowData($data)
    {
        $row = $data;
        $num = 1;
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (isset($item['value']) && is_string($item['value'])) {
                        $valText = $item['name'] . '：' . $item['value'];
                    } else {
                        $valText = $item['name'] . '：';
                    }
                    if (isset($item['key']) && $item['key'] == 'radio') {
                        if (isset($item['value'])) {
                            $valText = $item['name'] . "：" . $item['value'];
                        } else {
                            $valText = $item['name'] . "：";
                        }
                    }
                    if (isset($item['key']) && $item['key'] == 'checkbox') {
                        $valText = '';
                        if (isset($item['value'])) {
                            if (is_array($item['value'])) {
                                foreach ($item['value'] as $valItem) {
                                    $valText .= $valItem . '|';
                                }
                                $valText = substr($valText, 0, strlen($valText) - 1);
                            } else {
                                $valText = $item['value'];
                            }

                            $valText = $item['name'] . '：' . $valText;
                        } else {
                            $valText = $item['name'] . '：';
                        }
                    }
                    if (isset($item['key']) && $item['key'] == 'img_upload') {
                        $valText = '';
                        if (isset($item['value'])) {
                            if (is_array($item['value'])) {
                                foreach ($item['value'] as $valItem) {
                                    $valText .= $valItem . '|';
                                }
                                $valText = substr($valText, 0, strlen($valText) - 1);
                            } else {
                                $valText = $item['value'];
                            }
                            $valText = $item['name'] . '：' . $valText;
                        } else {
                            $valText = $item['name'] . '：';
                        }
                    }

                    $newValue = $this->check($valText);
                    if ($newValue != '') {
                        $row[$key + ($num++)] = mb_convert_encoding($newValue, 'GBK', 'UTF-8');
                    }
                }
                unset($row[$key]);
            } else {
                $value = $this->check($value);
                $row[$key] = mb_convert_encoding($value, 'GBK', 'UTF-8');
            }
        }

        $data = array_values($row);
        return $data;
    }
}