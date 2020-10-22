<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/19
 * Time: 9:31
 */

namespace app\plugins\aliapp\forms;


use app\core\response\ApiCode;
use app\models\AliappConfig;
use app\models\Model;

class SettingForm extends Model
{
    public $appid;
    public $alipay_public_key;
    public $app_private_key;
    public $cs_tnt_inst_id;
    public $cs_scene;
    public $app_aes_secret;
    public $transfer_app_id;
    public $transfer_app_private_key;
    public $transfer_alipay_public_key;
    public $transfer_appcert;
    public $transfer_alipay_rootcert;

    public function rules()
    {
        return [
            [['appid', 'alipay_public_key', 'app_private_key', 'cs_tnt_inst_id', 'cs_scene', 'app_aes_secret', 'transfer_app_id', 'transfer_app_private_key', 'transfer_alipay_public_key', 'transfer_appcert', 'transfer_alipay_rootcert'], 'trim'],
            [['appid', 'alipay_public_key', 'app_private_key',], 'required'],
            ['alipay_public_key', function () {
                $this->alipay_public_key = $this->addBeginAndEnd(
                    '-----BEGIN PUBLIC KEY-----',
                    '-----END PUBLIC KEY-----',
                    $this->alipay_public_key
                );
            }],
            ['app_private_key', function () {
                $this->app_private_key = $this->addBeginAndEnd(
                    '-----BEGIN RSA PRIVATE KEY-----',
                    '-----END RSA PRIVATE KEY-----',
                    $this->app_private_key
                );
            }],
            ['transfer_app_private_key', function () {
                $this->transfer_app_private_key = $this->addBeginAndEnd(
                    '-----BEGIN RSA PRIVATE KEY-----',
                    '-----END RSA PRIVATE KEY-----',
                    $this->transfer_app_private_key
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
        $model = AliappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            $model = new AliappConfig();
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
