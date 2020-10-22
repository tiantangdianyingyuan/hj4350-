<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/19
 * Time: 19:38
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\config;


use app\forms\common\prints\Exceptions\PrintException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class YilianyunConfig extends BaseConfig
{
    public $client_id; // 用户ID
    public $machine_code; // 打印机终端号
    public $client_key; // API密钥
    public $key; // 打印机密钥
    public $time;
    private $url = 'http://open.10ss.net:8888';

    public function print($content)
    {
        date_default_timezone_set("Asia/Shanghai");
        $result = $this->actionPrint($this->client_id, $this->machine_code, $content, $this->client_key, $this->key);
        if (!$result) {
            throw new PrintException('请求数据为空');
        }
        $result = json_decode($result, true);
        switch ($result['state']) {
            case 1:
                return true;
                break;
            case 2:
                throw new PrintException('提交时间超时。验证你所提交的时间戳超过3分钟后拒绝接受', $result, 1);
                break;
            case 3:
                throw new PrintException('参数有误', $result, 1);
                break;
            case 4:
                throw new PrintException('sign加密验证失败', $result, 1);
                break;
            default:
                throw new PrintException(isset($result['error']) ? $result['error'] : '易联云配置有问题', $result, 1);
                break;
        }
    }


    /**
     * 生成签名sign
     * @param  array $params 参数
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return   string sign
     */
    private function generateSign($params, $apiKey, $msign)
    {
        //所有请求参数按照字母先后顺序排
        ksort($params);
        //定义字符串开始所包括的字符串
        $stringToBeSigned = $apiKey;
        //把所有参数名和参数值串在一起
        foreach ($params as $k => $v) {
            $stringToBeSigned .= urldecode($k . $v);
        }
        unset($k, $v);
        //定义字符串结尾所包括的字符串
        $stringToBeSigned .= $msign;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * 生成字符串参数
     * @param array $param 参数
     * @return  string        参数字符串
     */
    public function getStr($param)
    {
        $str = '';
        foreach ($param as $key => $value) {
            $str = $str . $key . '=' . $value . '&';
        }
        $str = rtrim($str, '&');
        return $str;
    }

    /**
     * 打印接口
     * @param  int $partner 用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $content 打印内容
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return string
     */
    public function actionPrint($partner, $machine_code, $content, $apiKey, $msign)
    {
        $param = array(
            "partner" => $partner,
            'machine_code' => $machine_code,
            'time' => time(),
        );
        //获取签名
        $param['sign'] = $this->generateSign($param, $apiKey, $msign);
        $param['content'] = urlencode($content);
        $str = $this->getStr($param);
        return $this->sendCmd('http://open.10ss.net:8888/index.php', $str);
    }

    /**
     *  添加打印机
     * @param  int $partner 用户ID1
     * @param  string $machine_code 打印机终端号
     * @param  string $username 用户名
     * @param  string $printname 打印机名称
     * @param  string $mobilephone 打印机卡号
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return string
     */
    public function actionAddprint($partner, $machine_code, $username, $printname, $mobilephone, $apiKey, $msign)
    {
        $param = array(
            'partner' => $partner,
            'machine_code' => $machine_code,
            'username' => $username,
            'printname' => $printname,
            'mobilephone' => $mobilephone,
        );
        $param['sign'] = $this->generateSign($param, $apiKey, $msign);
        $param['msign'] = $msign;
        $str = $this->getStr($param);
        return $this->sendCmd('http://open.10ss.net:8888/addprint.php', $str);
    }

    /**
     * 删除打印机
     * @param  int $partner 用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return string
     */
    public function actionRemoveprinter($partner, $machine_code, $apiKey, $msign)
    {
        $param = array(
            'partner' => $partner,
            'machine_code' => $machine_code,
        );
        $param['sign'] = $this->generateSign($param, $apiKey, $msign);
        $str = $this->getStr($param);
        return $this->sendCmd('http://open.10ss.net:8888/removeprint.php', $str);
    }

    /**
     * 发起请求
     * @param  string $url 请求地址
     * @param  string $data 请求数据包
     * @return   string      请求返回数据
     */
    public function sendCmd($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }
}
