<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/3 15:02
 */


namespace luweiss\Wechat;


use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;

/**
 * Class Wechat
 * @package luweiss\Wechat
 */
class Wechat extends WechatBase
{
    const CACHE_TARGET_FILE = 'file';
    const CACHE_TARGET_REDIS = 'redis';
    const CACHE_TARGET_MEMCACHED = 'memcached';
    const CACHE_TARGET_APCU = 'apcu';

    public $appId;
    public $appSecret;
    public $cache;

    /** @var Cache $cacheObject */
    private $cacheObject;
    private $accessToken;
    private $accessTokenOk;

    /**
     * Wechat constructor.
     * @param array $config <br>
     * <br> [
     * <br>     'appId' => '',
     * <br>     'appSecret' => '',
     * <br>     'cache' =>
     * <br>             [
     * <br>                 'target' => Wechat::CACHE_TARGET_XXX,
     * <br>                 'dir' => '文件缓存目录',
     * <br>                 'host' => 'redis或memcached服务器',
     * <br>                 'port' => 'redis或memcached端口',
     * <br>             ],
     * <br> ]
     * @throws WechatException
     */
    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        $this->setCacheComponent();
    }

    private function setCacheComponent()
    {
        if (!$this->cache) {
            $dir = dirname(dirname(__DIR__)) . '/runtime/cache';
            $this->cacheObject = new FilesystemCache($dir);
            @chmod($dir, 0777);
        }
        $target = !empty($this->cache['target']) ? $this->cache['target'] : static::CACHE_TARGET_FILE;
        switch ($target) {
            case static::CACHE_TARGET_FILE:
                $dir = !empty($this->cache['dir']) ?
                    $this->cache['dir'] : (dirname(dirname(__DIR__)) . '/runtime/cache');
                $this->cacheObject = new FilesystemCache($dir);
                @chmod($dir, 0777);
                break;
            case static::CACHE_TARGET_REDIS:
                $host = !empty($this->cache['host']) ? $this->cache['host'] : '127.0.0.1';
                $port = !empty($this->cache['port']) ? $this->cache['port'] : 6379;
                $redis = new \Redis();
                $redis->connect($host, $port);
                if (!empty($this->cache['password'])) {
                    $redis->auth($this->cache['password']);
                }
                $this->cacheObject = new RedisCache();
                $this->cacheObject->setRedis($redis);
                break;
            case static::CACHE_TARGET_MEMCACHED:
                $host = !empty($this->cache['host']) ? $this->cache['host'] : '127.0.0.1';
                $port = !empty($this->cache['port']) ? $this->cache['port'] : 6379;
                $memcached = new \Memcached();
                if (!empty($this->cache['username']) && !empty($this->cache['password'])) {
                    $memcached->setSaslAuthData($this->cache['username'], $this->cache['password']);
                }
                $memcached->addServer($host, $port);
                $this->cacheObject = new MemcachedCache();
                $this->cacheObject->setMemcached($memcached);
                break;
            case static::CACHE_TARGET_APCU:
                $this->cacheObject = new ApcuCache();
                break;
            default:
                throw new WechatException('无效的cache target `' . $target . '`。');
                break;
        }
        return $this;
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */
    public function getClientResult($result)
    {
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            $msg = 'errCode: ' . $result['errcode'] . ', errMsg: ' . $result['errmsg'];
            throw new WechatException($msg);
        }
        return $result;
    }

    /**
     * 获取accessToken
     * @param bool $refresh 是否刷新access token，不从缓存获取
     * @return string
     * @throws WechatException
     */
    public function getAccessToken($refresh = false)
    {
        if (!$this->appId) {
            throw  new WechatException('appId 不能为空。');
        }
        if (!$this->appSecret) {
            throw  new WechatException('appSecret 不能为空。');
        }
        if ($this->accessToken) {
            return $this->accessToken;
        }
        $cacheKey = 'ACCESS_TOKEN_OF_APPID-' . $this->appId;
        if (!$refresh) {
            $this->accessToken = $this->cacheObject->fetch($cacheKey);
            if ($this->accessToken && $this->checkAccessToken()) {
                return $this->accessToken;
            }
        }
        $api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='
            . $this->appId
            . '&secret=' . $this->appSecret;
        $res = $this->getClient()->get($api);
        $this->accessToken = $res['access_token'];
        $this->cacheObject->save($cacheKey, $this->accessToken, 7000);
        return $this->accessToken;
    }

    /**
     * 检查accessToken有效性，若有效，则缓存3分钟
     * @return bool|mixed
     */
    private function checkAccessToken()
    {
        if (!$this->accessToken) {
            return false;
        }
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        $cacheKey = 'CHECK_ACCESS_TOKEN_OF_TOKEN-' . $this->accessToken;
        $this->accessTokenOk = $this->cacheObject->fetch($cacheKey);
        if ($this->accessTokenOk) {
            return $this->accessTokenOk;
        }
        $api = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$this->accessToken}";
        try {
            $this->getClient()->get($api);
            $this->accessTokenOk = true;
            $this->cacheObject->save($cacheKey, true, 180);
        } catch (\Exception $e) {
            $this->accessTokenOk = false;
        }
        return $this->accessTokenOk;
    }

    public function jsCodeToSession($code)
    {
        $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        return $this->getClient()->get($api);
    }

    public function decryptData($encryptedData, $iv, $code)
    {
        if (mb_strlen($iv) !== 24) {
            throw new WechatException('iv长度不正确，必须是24位。');
        }
        $sessionData = $this->jsCodeToSession($code);
        $sessionKey = $sessionData['session_key'];
        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result, true);
        if (!$dataObj) {
            return [
                'openId' => $sessionData['openid'],
                'nickName' => null,
                'avatarUrl' => null,
            ];
        }
        return $dataObj;
    }
}
