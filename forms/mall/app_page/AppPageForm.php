<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/16
 * Time: 10:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\app_page;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\Model;

class AppPageForm extends Model
{
    public $path;
    public $params;

    public function rules()
    {
        return [
            [['path', 'params'], 'trim'],
            [['path'], 'required'],
            [['params'], 'safe']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $qrcode = new CommonQrCode();
            $qrcode->appPlatform = 'all';
            if ($this->params) {
                $this->params = json_decode($this->params, true);
            }
            $list = $qrcode->getQrCode($this->params, 430, $this->path);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $list
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
