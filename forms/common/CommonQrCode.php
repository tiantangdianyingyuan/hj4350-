<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common;


use Alipay\AlipayRequestFactory;
use app\helpers\CurlHelper;
use app\models\Model;
use app\models\QrCodeParameter;
use Grafika\Color;
use Grafika\Grafika;
use Grafika\ImageInterface;
use GuzzleHttp\Client;

class CommonQrCode extends Model
{
    public $accessToken;
    public $appPlatform;


    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    private function getClient()
    {
        return new Client([
            'verify' => false,
            'Content-Type' => 'image/jpeg'
        ]);
    }

    private function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);

        return $response;
    }

    private function get($url, $params = array())
    {
        $response = $this->getClient()->get($this->buildParams($url, $params));
        return json_decode($response->getBody(), true);
    }

    private function buildParams($url, $array)
    {
        $query = http_build_query($array);
        $url = trim($url, '?');
        $url = trim($url, '&');
        if (mb_stripos($url, '?')) {
            return $url . '&' . $query;
        } else {
            return $url . '?' . $query;
        }
    }

    /**
     * @param array $scene 二维码参数
     * @param int $width 二维码大小
     * @param string $page 跳转页面
     * @return mixed    小程序类型 0--微信 1--支付宝
     * @throws \Exception
     */
    public function getQrCode($scene = [], $width = 430, $page = 'pages/index/index')
    {
        try {
            $appPlatform = $this->appPlatform;
            if (!$appPlatform) {
                $appPlatform = \Yii::$app->appPlatform;
            }
            // dd($appPlatform);
            if ($appPlatform == 'all') {
                try {
                    $wechat = $this->wechat($scene, $width, $page);
                } catch (\Exception $exception) {
                    throw $exception;
                    $wechat = [];
                }
                $alipay = [];
                return [
                    'wechat' => $wechat,
                    'alipay' => $alipay
                ];
            } elseif ($appPlatform == APP_PLATFORM_WXAPP) {
                return $this->wechat($scene, $width, $page);
            } elseif ($appPlatform == APP_PLATFORM_ALIAPP) {
                return $this->alipay($scene, \Yii::$app->mall->id, $page, '二维码');
            } elseif ($appPlatform == APP_PLATFORM_TTAPP) {
                return $this->toutiao($scene, $width, $page);
            } elseif ($appPlatform == APP_PLATFORM_BDAPP) {
                throw new \Exception('百度小程序暂不支持二维码生成');
            } else {
                throw new \Exception($appPlatform . '平台不存在');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private function wechat($scene, $width = 430, $page = null)
    {
        $this->accessToken = \Yii::$app->getWechat()->getAccessToken();
        if (!$this->accessToken) {
            throw new \Exception('微信配置有误');
        }

        $token = \Yii::$app->security->generateRandomString(30);
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$this->accessToken}";
        $res = $this->post($api, [
            'scene' => $token,
            'width' => $width,
        ]);

        if ($res->getStatusCode() == 200) {
            $parameter = $this->saveQrCodeParameter($token, $scene, $page);
            if (in_array('image/jpeg', $res->getHeader('Content-Type'))) {
                //返回图片
                $url = $this->saveTempImageByContent($res->getBody()->getContents());
                return [
                    //TODO temp
                    'file_path' => $url,
                ];
            } else {
                //返回文字
                $res = json_decode($res->getBody()->getContents(), true);
                throw new \Exception($res['errmsg']);
            }
        } else {
            throw new \Exception($res['errmsg']);
        }
    }

    /**
     * 小程序生成推广二维码接口
     * @param array $scene
     * @see https://docs.open.alipay.com/api_5/alipay.open.app.qrcode.create
     */
    private function alipay($scene, $storeId, $page = null, $describe = '')
    {
        $token = \Yii::$app->security->generateRandomString(30);
        $bizContent = [
            'url_param' => 'pages/index/index',
            'query_param' => "scene={$token}",
            'describe' => $describe ? $describe : '小程序码',
        ];
        try {
            /** @var \app\plugins\aliapp\Plugin $aliappPlugin */
            $aliappPlugin = \Yii::$app->plugin->getPlugin('aliapp');
            $aopClient = $aliappPlugin->getAliAopClient();
            $request = AlipayRequestFactory::create('alipay.open.app.qrcode.create', [
                'biz_content' => $bizContent,
            ]);
            $data = $aopClient->execute($request)->getData();
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage()
            ];
        }
        $ch = curl_init($data['qr_code_url']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $url = $this->saveTempImageByContent($result);
        $parameter = $this->saveQrCodeParameter($token, $scene, $page);
        return [
            'file_path' => $url,
        ];
    }

    /**
     * 字节跳动（头条）生成二维码
     * @param $scene
     * @param int $width
     * @param null|string $page
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    private function toutiao($scene, $width = 430, $page = null)
    {
        $width = $width < 280 ? 280 : $width;
        $token = \Yii::$app->security->generateRandomString(30);
        /** @var \app\plugins\ttapp\Plugin $plugin */
        $plugin = \Yii::$app->plugin->getPlugin('ttapp');
        $api = 'https://developer.toutiao.com/api/apps/qrcode';
        $appname = 'toutiao';
        try {
            if (isset(\Yii::$app->request->headers['X-tt-platform']) && !empty(\Yii::$app->request->headers['x-tt-platform'])) {
                $appname = \Yii::$app->request->headers['X-tt-platform'];
            }
        } catch (\Exception $e) {
            $appname = 'toutiao';
        }
        $body = [
            'access_token' => $plugin->getAccessToken(),
            'appname' => strtolower($appname),
            'path' => urlencode("pages/index/index?scene={$token}"),
            'width' => $width,
            'set_icon' => true,
        ];
        $client = new Client([
            'verify' => false,
        ]);
        $bodyJson = json_encode($body, JSON_UNESCAPED_UNICODE);
        $response = $client->post($api, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $bodyJson,
        ]);
        if ($response->getHeader('Content-Type')[0] != 'image/png') {
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            throw new \Exception("errcode ${result['errcode']}: {$result['errmsg']}");
        }
        $parameter = $this->saveQrCodeParameter($token, $scene, $page);
        $path = $this->saveTempImageByContent($response->getBody()->getContents());

        return [
            'code' => 0,
            'file_path' => $path,
        ];
    }

    //保存图片内容到临时文件
    private function saveTempImageByContent($content)
    {
        $imgName = md5(base64_encode($content)) . '.jpg';
        $save_path = \Yii::$app->basePath . '/web/temp/' . $imgName;
        if (!is_dir(\Yii::$app->basePath . '/web/temp')) {
            mkdir(\Yii::$app->basePath . '/web/temp');
        }
        $fp = fopen($save_path, 'w');
        fwrite($fp, $content);
        fclose($fp);

        if (\Yii::$app->appPlatform === APP_PLATFORM_ALIAPP) {
            $editor = Grafika::createEditor();
            /** @var ImageInterface $image */
            $editor->open($image, $save_path);
            $editor->crop($image, intval($image->getWidth() * 0.7942), intval($image->getWidth() * 0.7942), 'top-left', intval($image->getWidth() * 0.1026), intval($image->getHeight() * 0.1097));
            $editor->crop($image, intval($image->getWidth() * 1.414), intval($image->getHeight() * 1.414), 'center');
            $editor->fill($image, new Color('#ffffff'));
            $editor->save($image, $save_path);
        }

        if (\Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
            $editor = Grafika::createEditor();
            /** @var ImageInterface $image */
            $editor->open($image, $save_path);
            $editor->crop($image, intval($image->getWidth() * 1.414), intval($image->getHeight() * 1.414), 'center');
            $editor->fill($image, new Color('#ffffff'));
            $editor->save($image, $save_path);
        }

        return \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $imgName;
    }

    private function saveQrCodeParameter($token, $data, $page)
    {
        $model = new QrCodeParameter();
        $model->user_id = \Yii::$app->user->id ?: 0;
        $model->mall_id = \Yii::$app->mall->id;
        $model->token = $token;
        $model->path = $page;
        $model->data = \Yii::$app->serializer->encode($data);
        $res = $model->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($model));
        }

        return $model;
    }
}
