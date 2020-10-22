<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;

use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use yii\helpers\ArrayHelper;

class PosterForm extends GrafikaOption implements BasePoster
{
    public $platform;

    public function get()
    {
        try {
            $this->checkData();
            $common = new CommonScanCodePaySetting();
            $setting = $common->getSetting();

            if (isset($setting['poster']['bg_pic']['url']) && !$setting['poster']['bg_pic']['url']) {
                $setting['poster']['bg_pic']['url'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png';
            }

            if (isset($setting['poster']['qr_code'])) {
                $qrCode = new CommonQrCode();
                $qrCode->appPlatform = $this->platform;
                $code = $qrCode->getQrCode([], 240, 'plugins/scan_code/index/index');
                $code_path = self::saveTempImage($code['file_path']);
                if ($setting['poster']['qr_code']['type'] == 1) {
                    $code_path = self::avatar($code_path, $this->temp_path, $setting['poster']['qr_code']['size'], $setting['poster']['qr_code']['size']);
                }
                $setting['poster']['qr_code']['file_path'] = $this->destroyList($code_path);
            }

            $option = ArrayHelper::toArray($setting['poster']);
            $this->setFile($option);
            $editor = $this->getPoster($option);

            if (strstr(\Yii::$app->request->hostInfo, 'https') == false) {
                $editor->qrcode_url = str_replace('https://', 'http://', $editor->qrcode_url);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'url' => $editor->qrcode_url . '?v=' . time(),
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    private function checkData()
    {
        $list = \Yii::$app->plugin->getAllPlatformPlugins();
        $array = [];
        foreach ($list as $key => $value) {
            $array[] = $value->getName();
        }

        if (!in_array($this->platform, $array)) {
            throw new \Exception("平台参数不正确");
        }
    }
}
