<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 16:26
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\AttachmentUploadForm;
use app\forms\common\collect\Collect;
use app\models\AssistantData;
use yii\helpers\Json;

trait CollectData
{
    /**
     * @param $start
     * @param $end
     * @param $str
     * @return array
     * 正则截取函数
     */
    protected function pregSubstr($start, $end, $str) // 正则截取函数
    {
        $temp = preg_split($start, $str);
        $result = [];
        foreach ($temp as $index => $value) {
            if ($index == 0) {
                continue;
            }
            $content = preg_split($end, $value);
            array_push($result, $content[0]);
        }
        return $result;
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
    protected function uploadFile($path)
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
     * @param $url
     * @return mixed
     * @throws \Exception
     * 获取图片后缀
     */
    protected function getImageExtension($url)
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

    // 处理图片
    public function handleImg($url)
    {
        $temp = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($temp)) {
            mkdir($temp);
        }
        $temp = $temp . 'collect/';
        if (!is_dir($temp)) {
            mkdir($temp);
        }
        try {
            if (Collect::$is_download == 0) {
                throw new \Exception('关闭图片下载');
            }
            $file = substr(md5($url . time()), 16, 16) . '.' . $this->getImageExtension($url);
            $saveTo = $temp . $file;
            // 1、先将网络图片下载到本地临时存储
            $this->downloadFile($url, $saveTo);
            // 2、在上传到系统设置的存储上
            $newUrl = $this->uploadFile($saveTo);
            // 3、删除临时图片
            unlink($saveTo);
        } catch (\Exception $exception) {
            \Yii::warning($exception);
            $newUrl = $url;
        }
        return $newUrl;
    }

    public function saveData($type, $itemId, $data)
    {
        $form = new AssistantData();
        $form->type = $type;
        $form->itemId = $itemId;
        $form->json = Json::encode($data, JSON_UNESCAPED_UNICODE);
        $form->created_at = mysql_timestamp();
        if (!$form->save()) {
            \Yii::warning($this->getErrorMsg($form));
        }
    }

    public function changeImgUrl($item)
    {
        if (substr($item, 0, 4) != 'http') {
            return 'http:' . $item;
        }
        return $item;
    }
}
