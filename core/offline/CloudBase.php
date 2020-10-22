<?php
/**
 * @copyright ©2018 海西通商城
 * @author Lu Wei
 * @link http://www.haixitong.net/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:21:00
 */


namespace app\core\offline;

use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\CorePlugin;
use GuzzleHttp\Client;
use \Exception;
use yii\base\Component;

class CloudBase extends Component
{
    // public $classVersion = '4.2.10';
    public $urlEncodeQueryString = true;
    // todo 开发完成此处请切换
   //private $xBaseUrl = 'aHR0cHM6Ly9iZGF1dGguempoZWppYW5nLmNvbQ=='; // 正式
    private $xBaseUrl = 'aHR0cDovL2xvY2FsaG9zdC9iZGF1dGgvd2Vi'; // 开发
    private $xLocalAuthInfo;
    //优化多次DB查询使用
    private $cps;
    private $ad_count;

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     * @throws Exception
     */

    public function httpGet($url, $params = [])
    {

        throw new CloudException($res['msg'], $res['code'], null, $res);
        return $res['data'];
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws Exception
     */
    // public function httpPost($url, $params = [], $data = [])
    // {

    //     $res = json_decode($body, true);
    //     return $res['data'];
    // }

}
