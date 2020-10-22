<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/19
 * Time: 19:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\config;


use app\forms\common\prints\Exceptions\PrintException;

class KdtConfig extends BaseConfig
{
    public $name; // 打印机编号
    public $key; // 打印机密钥
    public $time; // 打印联数
    private $url = "http://open.printcenter.cn:8080/addOrder";

    public function print($content)
    {
        date_default_timezone_set("Asia/Shanghai");
        header("Content-Type: text/html;charset=utf-8");

        $selfMessage = array(
            'deviceNo' => $this->name,
            'printContent' => $content,
            'key' => $this->key,
            'times' => $this->time
        );
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded ",
                'method' => 'POST',
                'content' => http_build_query($selfMessage),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->url, false, $context);

        $result = json_decode($result, true);
        if ($result['responseCode'] != 0) {
            throw new PrintException($result['msg'], $result, 1);
        }
        return $result;
    }
}
