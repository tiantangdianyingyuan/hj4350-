<?php

namespace app\plugins\booking\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonOption;
use app\plugins\booking\models\BookingGoods;

class PosterForm extends GrafikaOption
{
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
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

    private function getGoods()
    {
        $bookingGoods = BookingGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse'])->one();
        if (!$bookingGoods) {
            throw new \Exception('预约商品不存在');
        }
        return $bookingGoods;
    }

    private function get()
    {
        $setting = CommonBooking::getSetting();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        $goods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $goods->goods->goodsWarehouse->cover_pic;
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        if (isset($option['price'])) {
            $price = array_column($goods->goods->attr, 'price');
            $price_str = max($price) > min($price) ? min($price) . '~' . max($price) : min($price);
            $option['price']['text'] = sprintf('￥%s', $price_str);

            if($price_str == 0) {
                $option['price']['file_path'] = \Yii::$app->basePath . '/web/statics/img/mall/poster/free.png';
                $option['price']['file_type'] = 'image';
                $option['price']['width'] = 120;
                $option['price']['height'] = 56;
            }
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['goods_id' => $goods->goods_id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/book/goods/goods'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}