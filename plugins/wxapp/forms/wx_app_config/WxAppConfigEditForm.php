<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\wxapp\forms\wx_app_config;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\forms\WechatServicePay;
use app\plugins\wxapp\models\WxappConfig;
use app\plugins\wxapp\models\WxappService;
use luweiss\Wechat\Wechat;
use luweiss\Wechat\WechatException;
use luweiss\Wechat\WechatPay;

class WxAppConfigEditForm extends Model
{
    public $appid;
    public $appsecret;
    public $cert_pem;
    public $key;
    public $key_pem;
    public $mchid;
    public $id;
    public $is_choise;
    public $service_appid;
    public $service_mchid;
    public $service_key;

    public function rules()
    {
        return [
            [['appid', 'appsecret', 'mchid', 'is_choise'], 'required'],
            [['appid', 'appsecret', 'key_pem', 'cert_pem', 'service_appid'], 'string'],
            [['key', 'mchid', 'service_mchid', 'service_key'], 'string', 'max' => 32],
            [['id'], 'integer'],
            [['is_choise'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid' => '小程序AppId',
            'appsecret' => '小程序appSecret',
            'key' => '微信支付Api密钥',
            'mchid' => '微信支付商户号',
            'service_appid' => '微信支付服务商appid',
            'service_key' => '微信支付服务商Api密钥',
            'service_mchid' => '微信支付服务商商户号',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /**@var WxappConfig $wxAppConfig**/
        $wxAppConfig = WxappConfig::find()
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->with('service')
            ->one();
        if (!$wxAppConfig) {
            $wxAppConfig = new WxappConfig();
        }
        // 检测参数是否有效
        if ($this->is_choise) {
            $wechatPay = new WechatServicePay([
                  'appId' => $this->service_appid,
                  'mchId' => $this->service_mchid,
                  'sub_appid' => $this->appid,
                  'sub_mch_id' => $this->mchid,
                  'key' => !empty($this->service_key) ? $this->service_key : $wxAppConfig->service->key,
              ]);
        } else {
            $wechatPay = new WechatPay([
               'appId' => $this->appid,
               'mchId' => $this->mchid,
               'key' => $this->key
            ]);
        }
        try {
            if (!$this->appid) {
                throw new \Exception('小程序AppId有误');
            }
            if (!$this->appsecret) {
                throw new \Exception('小程序appSecret有误');
            }
            $wechat = new Wechat([
                'appId' => $this->appid,
                'appSecret' => $this->appsecret,
            ]);
            $wechat->getAccessToken(true);
        } catch (WechatException $e) {
            if ($e->getRaw()['errcode'] == '40013') {
                $message = '小程序AppId有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
            if ($e->getRaw()['errcode'] == '40125') {
                $message = '小程序appSecret有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }

        try {
            if ($this->mchid || $this->key) {
                $wechatPay->orderQuery(['out_trade_no' => '88888888']);
            }
        } catch (WechatException $e) {
            if ($e->getRaw()['return_code'] != 'SUCCESS') {
                $message = '微信支付商户号 或 微信支付Api密钥有误(' . $e->getRaw()['return_msg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }


        try {
            $t = \Yii::$app->db->beginTransaction();
            $wxAppConfig->mall_id = \Yii::$app->mall->id;
            $wxAppConfig->appid = $this->appid;
            $wxAppConfig->appsecret = $this->appsecret;
            $wxAppConfig->key = !empty($this->key) ? $this->key : '';
            $wxAppConfig->mchid = !empty($this->mchid) ? $this->mchid : '';
            $wxAppConfig->key_pem = $this->key_pem ?? '';
            $wxAppConfig->cert_pem = $this->cert_pem ?? '';
            $res = $wxAppConfig->save();
            $wxAppService = WxappService::findOne(['cid' => $wxAppConfig->id]);
            if ($this->is_choise == 1) {
                if (!$wxAppService) {
                    $wxAppService = new WxappService();
                }
                $wxAppService->cid = $wxAppConfig->id;
                $wxAppService->is_choise = 1;
                $wxAppService->appid = $this->service_appid;
                $wxAppService->mchid = $this->service_mchid;
                $wxAppService->key = !empty($this->service_key) ? $this->service_key : $wxAppConfig->service->key;
                $res1 = $wxAppService->save();
                if ($res1) {
                    $t->commit();
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功',
                    ];
                } else {
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '保存失败',
                    ];
                }
            } elseif ($wxAppService) {
                $wxAppService->is_choise = 0;
                $wxAppService->save();
            }

            if ($res) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
