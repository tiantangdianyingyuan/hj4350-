<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express;

class ExpressFactory
{
    public function create(string $model, array $config = [])
    {
        $model = 'app\core\express\factory\\' . lcfirst($model) . '\\' . $model;
        if (class_exists($model)) {
            return new $model($config);
        }
        throw new \Exception('调用错误');
    }
}