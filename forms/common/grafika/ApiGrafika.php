<?php

namespace app\forms\common\grafika;

use Grafika\Color;
use Grafika\Grafika;

class ApiGrafika extends BaseGrafika
{
    protected $model;
    protected $qrcode_url;
    protected $destroy_list = [];

    public $temp_path;
    public $font_path;
    public $poster_file_name;
    public $default_avatar_url;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if ($config === []) {
            $config = get_supported_image_lib();
        }
        $this->model = Grafika::createEditor($config);
        $this->font_path = $this->setFont();
        $this->temp_path = $this->setTempPath();
        $this->default_avatar_url = $this->setDefaultAvatar();
        if (!is_dir($this->temp_path) && mkdir($this->temp_path)) return;
    }

    final public function destroyList(string $url)
    {
        if ($url && array_search($url, $this->destroy_list) === false) {
            array_push($this->destroy_list, $url);
        }
        return $url;
    }

    private function setFont():string
    {
        return \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';
    }

    private function setDefaultAvatar(): string
    {
        return \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/user-default-avatar.png';
    }

    private function setTempPath():string
    {
        return \Yii::$app->basePath . '/web/temp/';
    }

    protected function isHex2($color) :bool
    {
        return stripos($color, '#')  === 0;
    }

    protected function isUrl($code_path)
    {
        $code_path = trim($code_path);
        return stripos($code_path, 'http://')  === 0 || stripos($code_path, 'https://') === 0;
    }

    //REFUND
    public function apiText(&$image, $text, $size = 12, $x = 0, $y = 12, $color = null, $font = '', $angle = 0)
    {
        if ($font == '') {
            $font = $this->font_path;
        }

        $this->model->text($image, $text, $size, $x, $y, new Color($color), $font, $angle);
    }

    public function apiSave($image, $file = null, $type = null, $quality = null, $interlace = false, $permission = 0755)
    {
        $this->model->save($image, $this->temp_path . $this->poster_file_name, 'jpeg', 85);
        $this->qrcode_url = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $this->poster_file_name);
    }

    //强制
    public function apiBlend(&$image, &$only, $code_path, $newWidth, $newHeight, $type = 'normal', $opacity = 1.0, $position = 'top-left', $offsetX = 0, $offsetY = 0, $mode = 'exact')
    {
        $this->model->open($only, $code_path);
        $this->model->resize($only, $newWidth, $newHeight, $mode);
        $this->model->flatten($only);
        $this->model->blend($image, $only, $type, $opacity, $position, $offsetX, $offsetY);
    }

    public function __call($_method, $_arguments)
    {
        if (!is_callable(array(new CommonFunction(), $_method))) {
            throw new \Exception('Function not exists');
        }
        return call_user_func_array(array(new CommonFunction(), $_method), $_arguments);
    }

    public function __destruct()
    {
        foreach ($this->destroy_list as $v) {
            if (file_exists($v)) {
                \Yii::error(unlink($v));
            }
        }
    }
}