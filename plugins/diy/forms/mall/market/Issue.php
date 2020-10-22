<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/8
 * Time: 14:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall\market;


use Alchemy\Zippy\Zippy;
use app\forms\AttachmentUploadForm;
use app\models\Model;
use GuzzleHttp\Client;

class Issue extends Model
{
    public $type = 'encode'; // encode--导出到zip|decode--zip解压数据
    public $filePath; // 物理路径
    public $file = 'issue';
    public $fileList = [];
    public $urlPath; // 网络路径

    public function init()
    {
        $temp = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($temp)) {
            mkdir($temp);
        }
        $path = \Yii::$app->basePath . '/web/temp/' . $this->file;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $filePath = base64_encode(rand(0, 999999) . time());
        while (is_dir($path . '/' . $filePath)) {
            $filePath = base64_encode(rand(0, 999999) . time());
        }
        mkdir($path . '/' . $filePath);
        $this->filePath = $path . '/' . $filePath;
        $this->urlPath = \Yii::$app->request->hostInfo .
            \Yii::$app->request->baseUrl . '/temp/' . $this->file . '/' . $filePath;
    }

    /**
     * @param $list array
     * @throws \Exception
     * @return bool
     * 导入数据到zip
     */
    public function encode($list)
    {
        $list = $this->handler($list);
        // 保存模板数据
        $dataPath = $this->filePath . '/data.json';
        $dataJson = fopen($dataPath, 'wb');
        if ($dataJson === false) {
            throw new \Exception('发布失败,请检查站点目录是否有写入权限');
        }
        fwrite($dataJson, json_encode($list, JSON_UNESCAPED_UNICODE));
        fclose($dataJson);
        $this->fileList['data.json'] = $dataPath;
        $zippy = Zippy::load();
        $zippy->create($this->filePath . '/data.zip', $this->fileList);
        foreach ($this->fileList as $item) {
            unlink($item);
        }
        return $this->urlPath . '/data.zip';
    }

    /**
     * @param $package string 网络路径
     * @return array|false|mixed|string
     * @throws \Exception
     * 解压数据
     */
    public function decode($package)
    {
        try {
            $path = $this->downZip($package);
            $zippy = Zippy::load();
            $archive = $zippy->open($path);
            $archive->extract($this->filePath);
            $dataJson = $this->filePath . '/data.json';
            if (!file_exists($dataJson)) {
                throw new \Exception('发布失败,请检查站点目录是否有写入权限');
            }
            $list = file_get_contents($dataJson);
            if (!$list) {
                throw new \Exception('发布失败,请检查站点目录是否有写入权限');
            }
            $list = json_decode($list, true);
            $list = $this->handler($list);
            unset($archive);
            unlink($path);
            return $list;
        } catch (\Exception $exception) {
            unset($archive);
//            unlink($path);
            throw $exception;
        }
    }

    /**
     * @param $list
     * @param null $key
     * @return array|string
     * @throws \Exception
     * 修改图片路径
     */
    private function handler($list, $key = null)
    {
        if (is_array($list)) {
            $list = $this->unsetList($list);
            foreach ($list as $key => $item) {
                $list[$key] = $this->handler($item, $key);
            }
            return $list;
        } else {
            return $this->handlerString($key, $list);
        }
    }

    private function picKey()
    {
        return [
            'pic_url', 'picUrl', 'icon', 'goodsTagPicUrl', 'headerUrl', 'video_pic_url', 'notice_url', 'coupon_url',
            'coupon_not_url', 'discount_not_url', 'topic_url', 'topic_url_2', 'label_url', 'icon_url', 'logo_1',
            'logo_2', 'navPicUrl', 'scorePicUrl', 'backgroundPicUrl', 'couponPicUrl', 'goodsTagPicUrl', 'buy_bg',
            'renew_bg', 'closedPicUrl', 'openedPicUrl'
        ];
    }

    /**
     * @param $key string 图片对应的key
     * @param $str string 图片链接
     * @throws \Exception
     * @return string
     * 判断是否是图片
     */
    private function handlerString($key, $str)
    {
        $picKey = $this->picKey();
        if (in_array($key, $picKey) && $str) {
            try {
                if ($this->type == 'encode') {
                    $file = substr(md5($str . time()), 16, 16) . '.' . $this->getImageExtension($str);
                    $imgPath = $this->filePath . '/' . $file;
                    $this->downloadFile($str, $imgPath);
                    $this->fileList[$file] = $imgPath;
                    return $file;
                } else {
                    $path = $this->filePath . '/' . $str;
                    if (!file_exists($path)) {
                        throw new \Exception('发布失败,请检查站点目录是否有写入权限');
                    }
                    $url = $this->uploadFile($path);
                    return $url;
                }
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
        return $str;
    }

    /**
     * @param $url
     * @return mixed
     * @throws \Exception
     * 获取图片后缀
     */
    private function getImageExtension($url)
    {
        if (!function_exists('getimagesize')) {
            throw new \Exception('getimagesize函数无法使用');
        }
        $imgInfo = getimagesize($url);
        if (!$imgInfo) {
            throw new \Exception('无效的图片链接');
        }
        $arr = [
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
        ];
        if (!isset($arr[$imgInfo[2]])) {
            throw new \Exception('仅支持jpg、png格式的图片');
        }
        return $arr[$imgInfo[2]];
    }

    /**
     * @param $fileUrl string 原始地址
     * @param $saveTo string 保存后的地址
     * @throws \Exception
     * 下载图片
     */
    public function downloadFile($fileUrl, $saveTo)
    {
        $in = fopen($fileUrl, "rb");
        if ($in === false) {
            throw new \Exception('发布失败,请检查站点目录是否有写入权限');
        }
        $out = fopen($saveTo, "wb");
        if ($out === false) {
            throw new \Exception('发布失败,请检查站点目录是否有写入权限');
        }
        while ($chunk = fread($in, 8192)) {
            fwrite($out, $chunk, 8192);
        }
        fclose($in);
        fclose($out);
    }

    /**
     * @param $path
     * @return string
     * @throws \Exception
     * 上传图片到设置好的云存储
     */
    public function uploadFile($path)
    {
        $form = new AttachmentUploadForm();
        $form->file = AttachmentUploadForm::getInstanceFromFile($path);
        $res = $form->save();
        if ($res['code'] == 0) {
            $attachment = $res['data'];
            return $attachment->url;
        } else {
            throw new \Exception($res['msg']);
        }
    }

    /**
     * @param $fileUrl
     * @return string
     * @throws \Exception
     * 下载zip包到服务器
     */
    public function downZip($fileUrl)
    {
        $client = new Client([
            'verify' => false,
            'stream' => true,
        ]);
        $response = $client->get($fileUrl);
        $body = $response->getBody();
        $tempPath = $this->filePath;
        if (!is_dir($tempPath)) {
            make_dir($tempPath);
        }
        $tempFile = $tempPath . '/data.zip';
        $fp = fopen($tempFile, 'w+');
        if ($fp === false) {
            throw new \Exception('安装失败，请检查站点目录是否有写入权限。');
        }
        while (!$body->eof()) {
            fwrite($fp, $body->read(1024));
        }
        fclose($fp);
        if (!file_exists($tempFile)) {
            throw new \Exception('安装失败，请检查站点目录是否有写入权限。');
        }
        return $tempFile;
    }

    /**
     * @param $list
     * @return mixed
     * 移出商品组件、专题组件中列表数据
     */
    public function unsetList($list)
    {
        $ignore = ['goods', 'miaosha', 'pintuan', 'topic', 'integral-mall', 'booking', 'bargain', 'mch', 'advance', 'composition', 'gift'];
        foreach ($list as &$item) {
            if (isset($item['id']) && in_array($item['id'], $ignore)) {
                if (isset($item['data']['list'])) {
                    $item['data']['list'] = [];
                }
                if (isset($item['data']['catList'])) {
                    $item['data']['catList'] = [];
                }
                if (isset($item['data']['topic_list'])) {
                    $item['data']['topic_list'] = [];
                }
            }
        }
        unset($item);
        return $list;
    }
}
