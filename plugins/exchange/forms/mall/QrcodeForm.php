<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\forms\common\CommonQrCode;
use app\models\Model;
use app\plugins\exchange\models\ExchangeCode;

class QrcodeForm extends Model
{
    private $keyTemp = 'exchange-admin-qrcode:';
    private $tempList = [];
    public $code;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
        ];
    }

    public function setTempList()
    {
        $key = $this->keyTemp . \Yii::$app->mall->id;
        $this->tempList = \Yii::$app->cache->get($key) ?: [];
    }


    //是否是小程序码 缓存
    public function generate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $key = $this->keyTemp . \Yii::$app->mall->id;
            if (
                isset($this->tempList[$this->code])
                && $this->tempList[$this->code]
                && @file_get_contents(\Yii::$app->request->hostInfo . $this->tempList[$this->code])
            ) {
                $downUrl = $this->tempList[$this->code];
            } else {
                $qrocde = new CommonQrCode();
                $qrocde->appPlatform = APP_PLATFORM_WXAPP;
                $code = $qrocde->getQrCode(
                    ['code' => $this->code],
                    240,
                    'plugins/exchange/index/index'
                );
                $downUrl = parse_url($code['file_path'])['path'];
                $this->tempList[$this->code] = $downUrl;
                \Yii::$app->cache->set($key, $this->tempList, 2419200);//存缓存
            }
            return $downUrl . '?v=' . time();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function batchDownload($library_id)
    {
        $query = ExchangeCode::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'library_id' => $library_id,
        ]);
        $qrcode = new CommonQrCode();
        $qrcode->appPlatform = APP_PLATFORM_WXAPP;
        foreach ($query->each() as $item) {
            try {
                $code = $qrcode->getQrCode(
                    ['code' => $item->code],
                    240,
                    'plugins/exchange/index/index'
                );
                $url = parse_url($code['file_path'])['path'];
                $url = '/zjhj_mall_v4/web/temp/8160113861561f089a75107fc84fd96c.jpg';

            }catch(\Exception $e){
                dd($e);
            }

     //       return $url;
        }
    }
}
