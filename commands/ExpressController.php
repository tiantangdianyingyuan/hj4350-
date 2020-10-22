<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\commands;

use app\commands\express\Base;
use app\commands\express\ECommon;
use yii\console\Controller;

class ExpressController extends Controller
{
    use ECommon;

    public $attributes = [];

    public function actionFlush()
    {
        $dir = scandir(__DIR__ . '//express');
        for ($i = 0; $i < count($dir); $i++) {
            if (!in_array($dir[$i], ['.', '..', 'Base.php'])) {
                $class = current(explode('.', $dir[$i]));
                $nameClass = '\\app\\commands\\express\\' . $class;
                if (!class_exists($nameClass)) {
                    continue;
                }
                $child = new \ReflectionClass(new $nameClass());
                $Rbase = new \ReflectionClass(Base::class);
                if (!$child->isSubclassOf($Rbase)) {
                    continue;
                }
                $this->attributes[] = $child;
            }
        }
        $arr = [];
        $Express = \app\models\Express::getExpressList();
        foreach ($Express as $item) {
            $codes = [];
            for ($j = 0; $j < count($this->attributes); $j++) {
                /** @var \ReflectionClass $ReflectionClass */
                $ReflectionClass = $this->attributes[$j];
                $name = $ReflectionClass->getName();

                $codes[ltrim($name, $ReflectionClass->getNamespaceName())] = (new $name())->select($item['name']);
            }
            $arr[] = [
                'name' => $item['name'],
                'alias' => $codes,
            ];
        }

        $this->encrypt('express_list.json', $arr);
        $this->ok('[文件生成成功][OK]');
    }

    public function actionExpress($type = '')
    {
        $class = '\\app\\commands\\express\\' . $type;
        if (!class_exists($class)) {
            $this->err('参数错误');
            exit;
        }
        $model = new $class();
        $model->create();
    }
}
