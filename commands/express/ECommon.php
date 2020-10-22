<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\commands\express;

trait ECommon
{
    protected function ok($msg)
    {
        echo "\033[32m$msg\033[0m", PHP_EOL;
    }

    protected function err($msg)
    {
        echo $msg . PHP_EOL;
    }

    protected function encrypt($fileName, $content)
    {
        $fileUrl = $this->getBathUrl($fileName);
        if (!is_string($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }
        return file_put_contents($fileUrl, $content);
    }

    protected function getBathUrl($file)
    {
        return \Yii::$app->basePath . '/web/statics/text/' . $file;
    }

    protected function decrypt($fileName)
    {
        $fileUrl = $this->getBathUrl($fileName);
        if (!is_file($fileUrl) || !is_readable($fileUrl)) {
            return false;
        }
        $file = file_get_contents($fileUrl);
        return json_decode($file, true);
    }
}
