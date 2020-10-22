<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\Option;
use app\plugins\wxapp\models\WxappConfig;

class WechatAppImporting extends BaseImporting
{
    public function import()
    {
        try {
            $config = new WxappConfig();
            $config->mall_id = $this->mall->id;
            $config->appid = $this->v3Data['app_id'];
            $config->appsecret = $this->v3Data['app_secret'];
            $config->mchid = $this->v3Data['mch_id'];
            $config->key = $this->v3Data['key'];
            $config->cert_pem = $this->v3Data['cert_pem'];
            $config->key_pem = $this->v3Data['key_pem'];
            $config->created_at = date('Y-m-d H:i:s', $this->v3Data['addtime']);
            $config->updated_at = date('Y-m-d H:i:s', $this->v3Data['addtime']);
            $res = $config->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($config));
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}