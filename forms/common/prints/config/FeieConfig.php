<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/19
 * Time: 19:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\config;

use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\sdk\FeieYun;

class FeieConfig extends BaseConfig
{
    public $sn; // 打印机编号
    public $key; // 打印机密钥
    public $time; // 打印联数
    public $user; // 飞鹅云后台注册用户名
    public $ukey; // 飞鹅云后台登录生成的UKEY
    private $ip = 'api.feieyun.cn';
    private $path = '/Api/Open/';

    public function print($content)
    {
        date_default_timezone_set("Asia/Shanghai");
        $time = time();
        $content = array(
            'user' => $this->user,
            'stime' => $time,
            'sig' => sha1($this->user . $this->ukey . $time),
            'apiname' => 'Open_printMsg',

            'sn' => $this->sn,
            'content' => $content,
            'times' => $this->time
        );

        $client = new FeieYun($this->ip, 80);
        if (!$client->post($this->path, $content)) {
            throw new PrintException($client->getError(), $client->getError(), 1);
        }
        $result = $client->getContent();
        $result = json_decode($result, true);
        if ($result['ret'] != 0) {
            throw new PrintException($result['msg'], $result, 1);
        }
        return $result;
    }
}
