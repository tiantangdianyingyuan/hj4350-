<?php
/**
 * @copyright ©2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/7 10:05:00
 */


namespace app\core\offline;
    class CloudAuth {
    // public $classVersion = '4.2.10';
    public function getAuthInfo() {
        return [
            'host' => [
                'account_num' => 9999999999,
            ],
        ];
    }
    
}





use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\CorePlugin;
use GuzzleHttp\Client;
use \Exception;
use yii\base\Component;

/**
 * @property CloudCollect $collect
 * @property CloudTemplate $template
 */
class Cloud extends Component
{
    public $urlEncodeQueryString = true;
    // todo 开发完成此处请切换
//    private $xBaseUrl = 'aHR0cHM6Ly9iZGF1dGguempoZWppYW5nLmNvbQ=='; // 正式
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
        // $url = $this->getUrl($url);
        // $url = $this->appendParams($url, $params);
        // $body = $this->curlRequest('get', $url);
        // $res = json_decode($body, true);
//        if (!$res) {
//            throw new \Exception('Cloud response body `' . $body . '` could not be decode.');
//        }
//        if ($res['code'] !== 0) {
//            if ($res['code'] === -1) {
//                throw new CloudNotLoginException($res['msg']);
//            } else {
                throw new CloudException($res['msg'], $res['code'], null, $res);
//            }
//        }
        return $res['data'];
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws Exception
     */
    public function httpPost($url, $params = [], $data = [])
    {
        // $url = $this->getUrl($url);
        // $url = $this->appendParams($url, $params);
        // $body = $this->curlRequest('post', $url, $data);
        $res = json_decode($body, true);
        // if (!$res) {
        //     throw new CloudException($res['msg'], $res['code'], null, $res);
        // }
        return $res['data'];
    }
    // public $classVersion = '4.2.10';

    /** @var CloudBase $auth */
    public $base;

    /** @var CloudAuth $auth */
    public $auth;

    /** @var CloudPlugin $plugin */
    public $plugin;

    /** @var CloudUpdate $update */
    // public $update;

    /** @var CloudWxapp $wxapp */
    // public $wxapp;

    /** @var CloudCollect $collect */
    // public $collect;

    /** @var CloudTemplate $template */
    // public $template;

    public function init()
    {
        parent::init();
        // $this->base = new CloudBase();
        $this->auth = new CloudAuth();
        $this->plugin = new CloudPlugin();
        // $this->update = new CloudUpdate();
        // $this->wxapp = new CloudWxapp();
        // $this->collect = new CloudCollect();
        $this->template = new CloudTemplate();
    }
}
