<?php

namespace app\forms\mall\live;

use app\forms\AttachmentUploadForm;
use app\models\Model;
use Grafika\Grafika;

class CommonUpload extends Model
{
    /**
     * [uploadImage description]
     * @param  string $accessToken [微信token]
     * @param  string $picUrl      [图片链接]
     * @param  int    $size        [图片大小限制 单位:MB]
     * @param  array  $pxSize      [图片宽高限制]
     * @param  array  $autoExactPx [自动裁剪]
     * @return string              [图片ID]
     */
    public function uploadImage($accessToken, $picUrl, $size = 10, $pxSize = array(), $autoExactPx = array())
    {
        $filename = md5($picUrl) . '.jpg';
        $path = \Yii::$app->basePath . '/web/temp/live/' . date('Y') . date('m') . date('d') . '/';
        $localUrl = $path . $filename;
        try {
            $content = $this->getCurlContents($picUrl);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fp = fopen($localUrl, "a"); //将文件绑定到流
            fwrite($fp, $content); //写入文件

            $imageInfoArray = getimagesize($localUrl);
            $width = $imageInfoArray[0];
            $height = $imageInfoArray[1];
            if (!empty($pxSize)) {
                if ($width > $pxSize[0] || $height > $pxSize[1]) {
                    throw new \Exception("图片宽高最大限制为" . $pxSize[0] . '*' . $pxSize[1]);
                }
            }

            $imageInfo = (new AttachmentUploadForm())->getInstanceFromFile($localUrl);
            if ($imageInfo->size >= $size * 1024 * 1024) {
                throw new \Exception("图片大小不能超过" . $size . 'MB');
            }

            // 自动裁剪
            if (!empty($autoExactPx)) {
                $editor = Grafika::createEditor();
                $editor->open($image, $localUrl);
                $editor->resizeExact($image, $autoExactPx[0], $autoExactPx[1]);
                $res = $editor->save($image, $path . $filename, null, 90);
            }

            $api = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$accessToken}&type=image";
            $res = CommonLive::postFile($api, [
                [
                    'name' => 'media',
                    'contents' => fopen($imageInfo->tempName, 'r'),
                ],
            ]);

            $res = json_decode($res->getBody()->getContents(), true);

            if (isset($res['media_id'])) {
                $this->removeFile($localUrl);
                return $res['media_id'];
            } else {
                throw new \Exception($res['errmsg']);
            }
        } catch (\Exception $exception) {
            $this->removeFile($localUrl);
            throw $exception;
        }
    }

    private function removeFile($localUrl)
    {
        if (file_exists($localUrl)) {
            unlink($localUrl);
        }
    }

    private function getCurlContents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $returnContent = ob_get_contents();
        ob_end_clean();

        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($returnCode != 200) {
            $returnContent = file_get_contents($url);
            if (!$returnContent) {
                throw new \Exception('图片异常');
            }
        }

        return $returnContent;
    }
}
