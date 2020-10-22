<?php

namespace app\forms\common\grafika;

use GuzzleHttp\Client;

class CommonFunction
{
    use CustomizeFunction;

    public static function setName($text)
    {
        if (mb_strlen($text, 'UTF-8') > 8) {
            $text = mb_substr($text, 0, 8, 'UTF-8') . '...';
        }
        return $text;
    }

    /**
     * @param integer $fontsize 字体大小
     * @param integer $angle 角度
     * @param string $fontface 字体名称
     * @param string $string 字符串
     * @param integer $width 预设宽度
     * @return string
     */
    public static function autowrap($fontsize, $angle, $fontface, $string, $width, $max_line = null)
    {
        $content = "";
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        $letter = [];
        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $letter[] = mb_substr($string, $i, 1, 'UTF-8');
        }
        $line_count = 0;
        foreach ($letter as $l) {
            $teststr = $content . " " . $l;
            $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
            // 判断拼接后的字符串是否超过预设的宽度
            if (($testbox[2] > $width) && ($content !== "")) {
                $line_count++;
                if ($max_line && $line_count >= $max_line) {
                    $content = mb_substr($content, 0, -1, 'UTF-8') . "...";
                    break;
                }
                $content .= "\n";
            }
            $content .= $l;
        }

        return $content;
    }

    //获取网络图片到临时目录
    public static function saveTempImage($url, $default_url = '')
    {
        $url = trim($url) ?: trim($default_url);
        $wdcp_patch = false;
        $wdcp_patch_file = \Yii::$app->basePath . '/patch/wdcp.json';
        if (file_exists($wdcp_patch_file)) {
            $wdcp_patch = json_decode(file_get_contents($wdcp_patch_file), true);
            if ($wdcp_patch && in_array(\Yii::$app->request->hostName, $wdcp_patch)) {
                $wdcp_patch = true;
            } else {
                $wdcp_patch = false;
            }
        }
        if ($wdcp_patch) {
            $url = str_replace('http://', 'https://', $url);
        }

        if (!is_dir(\Yii::$app->runtimePath . '/image')) {
            mkdir(\Yii::$app->runtimePath . '/image');
        }
        $save_path = \Yii::$app->runtimePath . '/image/' . md5($url) . '.jpg';

        $client = new Client([
            'verify' => false,
            'headers' => [
                'Referer' => \Yii::$app->request->headers->get('referer', \Yii::$app->request->hostInfo),
            ],
        ]);
        $response = $client->get($url, ['save_to' => $save_path]);
        if ($response->getStatusCode() == 200) {
            return $save_path;
        } else {
            throw new \Exception('保存失败');
        }
    }

    /**
     * 画圆
     * @param $image_url
     * @param string $path
     * @return string
     */
    public static function avatar($image_url, $path = './')
    {
        list($w, $h) = getimagesize($image_url);
        $dest_path = $path . uniqid('r', true) . '.png';
        $src = imagecreatefromstring(file_get_contents($image_url));

        $new_pic = imagecreatetruecolor($w, $h);
        imagealphablending($new_pic, false);

        $transparent = imagecolorallocatealpha($new_pic, 0, 0, 0, 127);

        $r = $w / 2;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $color = imagecolorat($src, $x, $y);
                $_x = $x - $w / 2;
                $_y = $y - $h / 2;
                if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
                    imagesetpixel($new_pic, $x, $y, $color);
                } else {
                    imagesetpixel($new_pic, $x, $y, $transparent);
                }
            }
        }

        imagesavealpha($new_pic, true);
        imagepng($new_pic, $dest_path);
        imagedestroy($new_pic);
        imagedestroy($src);
        unlink($image_url);
        return $dest_path;
    }

    /**
     * 解决莫些挑剔客户；微信绿色框
     * @param $image_url
     * @param string $path
     * @return string
     */
    public static function wechatCode($image_url, $path = './')
    {
        define('MARGIN', 10, false);

        list($w, $h) = getimagesize($image_url);
        $dest_path = $path . uniqid('r', true) . '.png';
        $src = imagecreatefromstring(file_get_contents($image_url));

        $create_big_pic = imagecreatetruecolor($w + MARGIN * 2, $h + MARGIN * 2);
        imagealphablending($create_big_pic, false);

        $white = imagecolorallocate($create_big_pic, 255, 255, 255);
        imagefill($create_big_pic, 0, 0, $white);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $color = imagecolorat($src, $x, $y);
                imagesetpixel($create_big_pic, $x + MARGIN, $y + MARGIN, $color);
            }
        }
        imagesavealpha($create_big_pic, true);
        imagepng($create_big_pic, $dest_path);
        imagedestroy($create_big_pic);
        imagedestroy($src);
        unset($image_url);

        return self::avatar($dest_path, $path);
    }

    //图片 source 路径
    public static function image_center_crop(string $source, $width, $height, string $target)
    {
        if (!file_exists($source)) return false;

        /* 根据类型载入图像 */
        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($source);
                break;
        }
        if (!isset($image)) return false;

        /* 获取图像尺寸信息 */
        $target_w = $width;
        $target_h = $height;
        $source_w = imagesx($image);
        $source_h = imagesy($image);
        /* 计算裁剪宽度和高度 */
        $judge = (($source_w / $source_h) > ($target_w / $target_h));
        $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
        $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
        $start_x = $judge ? ($resize_w - $target_w) / 2 : 0;
        $start_y = !$judge ? ($resize_h - $target_h) / 2 : 0;
        /* 绘制居中缩放图像 */
        $resize_img = imagecreatetruecolor($resize_w, $resize_h);
        imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
        $target_img = imagecreatetruecolor($target_w, $target_h);
        imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
        /* 将图片保存至文件 */
        if (!file_exists(dirname($target))) mkdir(dirname($target), 0777, true);
        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                imagejpeg($target_img, $target);
                break;
            case IMAGETYPE_PNG:
                imagepng($target_img, $target);
                break;
            case IMAGETYPE_GIF:
                imagegif($target_img, $target);
                break;
        }
        return boolval(file_exists($target));
    }
}