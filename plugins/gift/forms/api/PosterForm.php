<?php

namespace app\plugins\gift\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\forms\common\CommonOption;
use app\plugins\gift\models\GiftLog;

class PosterForm extends GrafikaOption
{
    public $gift_id;

    public function rules()
    {
        return [
            [['gift_id'], 'integer'],
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

    private function getGift()
    {
        $gift = GiftLog::find()->where([
            'id' => $this->gift_id,
        ])->with(['sendOrder.detail.goods.goodsWarehouse'])->asArray()->one();
        if (!$gift) {
            throw new \Exception('礼物不存在');
        }
        return $gift;
    }

    private function get()
    {
        $setting = CommonGift::getSetting();
        $option = $this->optionDiff($setting['poster'], CommonOption::getPosterDefault());
        $gift = $this->getGift();

        //分享
        if (isset($option['pic'])) {
            if (count($gift['sendOrder']) > 1 || count($gift['sendOrder'][0]['detail']) > 1) {
                $option['pic']['file_path'] = $setting['poster']['pic']['pic_url'];
            } else {
                $option['pic']['file_path'] = $gift['sendOrder'][0]['detail'][0]['goods']['goodsWarehouse']['cover_pic'];
            }
        }

        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname . '送你一份礼物';

        isset($option['hide_desc']) && $option['hide_desc']['text'] = ($setting['auto_refund'] ?? 0) . '天内无人领取将自动退款';

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['gift_id' => $gift['id'], 'user_id' => \Yii::$app->user->id], 240, 'plugins/gift/index/index'
        ], $this);
        //使海报名称每次都不同，使之每次重新生成
        $keys = array_merge(
            $option,
            [
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'time' => time() . rand(1, 999999)
            ]
        );
        $this->poster_file_name = sha1(serialize($keys)) . '.jpg';

        $editor = $this->getPoster($option);

        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}