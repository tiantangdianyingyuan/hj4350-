<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\models\UserInfo;
use app\plugins\step\forms\common\CommonOption;
use app\plugins\step\forms\common\CommonStep;

class StepPosterForm extends GrafikaOption
{
    public $pic_id;
    public $num;

    public function rules()
    {
        return [
            [['pic_id', 'num'], 'required'],
            [['pic_id', 'num'], 'integer'],
        ];
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }


    private function get()
    {
        $setting = CommonStep::getSetting();

        if (!$setting['qrcode_pic']) {
            throw new \Exception('后台海报尚未配置');
        }

        $qrcode_list = array_column($setting['qrcode_pic'], 'url', 'id');

        if (!$this->pic_id || !array_key_exists($this->pic_id, $qrcode_list)) {
            throw new \Exception('数据异常');
        }

        $option = $this->optionDiff($setting['step_poster'], CommonOption::getStepPosterDefault());

        isset($option['pic']) && $option['pic']['file_path'] = $qrcode_list[$this->pic_id];
        isset($option['name']) && $option['name']['text'] = sprintf("已走了%s步", $this->num);
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['user_id' => \Yii::$app->user->id],
            240,
            'plugins/step/index/index'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}
