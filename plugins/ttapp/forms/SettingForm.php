<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/14
 * Time: 9:22
 */

namespace app\plugins\ttapp\forms;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\ttapp\models\TtappConfig;

class SettingForm extends Model
{
    public $app_key;
    public $app_secret;
    public $mch_id;
    public $pay_app_id;
    public $pay_app_secret;
    public $alipay_app_id;
    public $alipay_public_key;
    public $alipay_private_key;

    public function rules()
    {
        return [
            [['app_key', 'app_secret', 'mch_id', 'pay_app_id','pay_app_secret','alipay_app_id', 'alipay_private_key','alipay_public_key'], 'trim'],
            [['app_key', 'app_secret', 'mch_id', 'pay_app_id','pay_app_secret','alipay_app_id', 'alipay_private_key','alipay_public_key'], 'required'],
            ['alipay_public_key', function () {
                $this->alipay_public_key = $this->addBeginAndEnd(
                    '-----BEGIN PUBLIC KEY-----',
                    '-----END PUBLIC KEY-----',
                    $this->alipay_public_key
                );
            }],
            ['alipay_private_key', function () {
                $this->alipay_private_key = $this->addBeginAndEnd(
                    '-----BEGIN RSA PRIVATE KEY-----',
                    '-----END RSA PRIVATE KEY-----',
                    $this->alipay_private_key
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
        $model = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            $model = new TtappConfig();
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