<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/14
 * Time: 9:22
 */

namespace app\plugins\bdapp\forms;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\bdapp\models\BdappConfig;

class SettingForm extends Model
{
    public $app_id;
    public $app_key;
    public $app_secret;
    public $pay_dealid;
    public $pay_app_key;
    public $pay_private_key;
    public $pay_public_key;

    public function rules()
    {
        return [
            [['app_id', 'app_key', 'app_secret', 'pay_dealid', 'pay_app_key', 'pay_private_key', 'pay_public_key'], 'trim'],
            [['app_id', 'app_key', 'app_secret', 'pay_dealid', 'pay_app_key', 'pay_private_key', 'pay_public_key'], 'required'],
            ['pay_public_key', function () {
                $this->pay_public_key = $this->addBeginAndEnd(
                    '-----BEGIN PUBLIC KEY-----',
                    '-----END PUBLIC KEY-----',
                    $this->pay_public_key
                );
            }],
            ['pay_private_key', function () {
                $this->pay_private_key = $this->addBeginAndEnd(
                    '-----BEGIN RSA PRIVATE KEY-----',
                    '-----END RSA PRIVATE KEY-----',
                    $this->pay_private_key
                );
            }],
        ];
    }

    private function addBeginAndEnd($beginStr, $endStr, $data)
    {
        $data = $this->pregReplaceAll('/---.*---/', '', $data);
        $data = trim($data);
        $data = str_replace("\n", '', $data);
        $data = str_replace("\r\n", '', $data);
        $data = str_replace("\r", '', $data);
        $data = wordwrap($data, 64, "\r\n", true);

        if (mb_stripos($data, $beginStr) === false) {
            $data = $beginStr . "\r\n" . $data;
        }
        if (mb_stripos($data, $endStr) === false) {
            $data = $data . "\r\n" . $endStr;
        }
        return $data;
    }

    private function pregReplaceAll($find, $replacement, $s)
    {
        while (preg_match($find, $s)) {
            $s = preg_replace($find, $replacement, $s);
        }
        return $s;
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            $model = new BdappConfig();
            $model->mall_id = \Yii::$app->mall->id;
        }
        $model->attributes = $this->attributes;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}