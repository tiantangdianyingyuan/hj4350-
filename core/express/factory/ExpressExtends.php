<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory;

use app\core\express\exception\Kd100Exception;

abstract class ExpressExtends
{
    protected $express_no;
    protected $express_code;
    protected $mobile;
    protected $config;
    protected $key;
    protected const EXPRESS_PATH = 'statics/text/express_list.json';

    public function __construct($config = [])
    {
        $classArr = explode('\\', get_class($this));
        $this->key = strtolower(end($classArr));
        $this->config = $config;
    }

    final protected function getExpressCode($express_name)
    {
        if (!is_file(self::EXPRESS_PATH) || !is_readable(self::EXPRESS_PATH)) {
            throw new \Exception('文件读取失败');
        }
        $file = file_get_contents(self::EXPRESS_PATH);
        $list = json_decode($file, true);
        $arr = array_column($list, 'alias', 'name');
        if (!isset($arr[$express_name]) || empty($arr[$express_name][$this->key])) {
            $extra = $this->extraExpressCode();
            if (empty($extra)) {
                throw new Kd100Exception(sprintf('%s查询失败 %s', $this->key, $express_name));
            } else {
                \Yii::error($extra);
                return $extra;
            }
        }
        return $arr[$express_name][$this->key];
    }

    //自动识别
    protected function extraExpressCode()
    {
        return "";
    }
}
