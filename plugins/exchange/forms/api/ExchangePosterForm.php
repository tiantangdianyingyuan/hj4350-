<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\exchange\forms\common\CommonOption;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\models\ExchangeCode;

class ExchangePosterForm extends GrafikaOption
{
    public $code;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
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

    private function getCodeModel(): ?ExchangeCode
    {
        return ExchangeCode::find()->where([
            'code' => $this->code,
            'mall_id' => \Yii::$app->mall->id
        ])->one();
    }

    private function formatWidth($key, &$option)
    {
        isset($option[$key]) && $option[$key]['text'] = self::autowrap($option['exchange_prompt']['font'], 0, $this->font_path, $option[$key]['text'], $option[$key]['width']);
    }

    private function get()
    {
        $codeModel = $this->getCodeModel();
        if (!$codeModel) {
            throw new \Exception('数据不存在');
        }
        $setting = (new CommonSetting())->get();
        $option = $this->optionDiff($setting['poster'], CommonOption::getPosterDefault());
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        $this->formatWidth('nickname', $option);
        $this->formatWidth('exchange_prompt', $option);
        $this->formatWidth('big_title', $option);
        $this->formatWidth('small_title', $option);
        $this->formatWidth('message', $option);
        $this->formatWidth('desc', $option);
        $this->formatWidth('code', $option);

        isset($option['code']) && $option['code']['text'] = $this->code;
        if (isset($option['valid_time'])) {
            $mode = $codeModel->library->expire_type;
            if ($mode === 'all') {
                $text = '永久有效';
            } else {
                $text = sprintf('%s-%s', (new \DateTime($codeModel->valid_start_time))->format('Y.m.d'), (new \DateTime($codeModel->valid_end_time))->format('Y.m.d'));
            }
            $option['valid_time']['text'] = $text;
        }


        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['code' => $this->code, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/exchange/gift/gift',
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);
        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}
