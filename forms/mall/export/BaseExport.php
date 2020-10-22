<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;


use app\models\Model;

abstract class BaseExport extends Model
{
    public $fieldsKeyList;
    public $fieldsNameList;
    public $dataList;

    abstract public function fieldsList();

    abstract public function export($query);

    abstract protected function transform($list);

    protected function getFields()
    {
        $fieldsList = $this->fieldsList();
        $newFields = [];

        if ($this->fieldsKeyList) {
            foreach ($this->fieldsKeyList as $field) {
                foreach ($fieldsList as $item) {
                    if ($item['key'] === $field) {
                        $newFields[] = $item['value'];
                    }
                }
            }
        } else {
            $this->fieldsKeyList = [];
        }

        if ($this->getIsAddNumber()) {
            array_unshift($newFields, '序号');
            $this->fieldsKeyList = array_merge(['number'], $this->fieldsKeyList ?: []);
        }

        $this->fieldsNameList = $newFields;
    }

    protected function getDataList()
    {
        $newData = [];
        foreach ($this->dataList as $key => $item) {
            $arr = [];
            foreach ($this->fieldsKeyList as $fieldsKey) {
                if (isset($item[$fieldsKey])) {
                    $arr[] = $item[$fieldsKey];
                } else {
                    $arr[] = '';
                }
            }
            $newData[] = $arr;
        }
        return $newData;
    }

    protected function getPlatform($platform)
    {
        switch ($platform) {
            case 'wxapp':
                $value = '微信';
                break;
            case 'aliapp':
                $value = '支付宝';
                break;
            case 'ttapp':
                $value = '头条';
                break;
            case 'bdapp':
                $value = '百度';
                break;
            default:
                $value = '未知';
                break;
        }

        return $value;
    }

    protected function getDateTime($dateTime)
    {
        return (int)$dateTime > 0 ? (string)$dateTime : '';
    }

    protected function getIsAddNumber()
    {
        return true;
    }
}
