<?php

namespace app\plugins\miaosha\forms\api\v2;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\miaosha\forms\common\v2\CommonOption;
use app\plugins\miaosha\forms\common\v2\SettingForm;
use app\plugins\miaosha\models\MiaoshaGoods;

class MsPosterForm extends GrafikaOption
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
        $msGoods = MiaoshaGoods::find()->where([
            'is_delete' => 0,
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse', 'goods.attr'])->one();

        if (!$msGoods) {
            throw new \Exception('秒杀商品不存在');
        }
        return $msGoods;
    }

    private function get()
    {
        $setting = (new SettingForm())->search();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());

        $goods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $goods->goods->goodsWarehouse->cover_pic;
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['time_str']) && $option['time_str']['text'] = date('m.d', strtotime($goods->open_date)) . ' ' . $goods->open_time . ':00场';

        if (isset($option['price'])) {
            $attr = $goods->goods->attr;
            $price = array_column($attr, 'price');
            $price_str = max($price) > min($price) ? '￥' . min($price) . '~' . max($price) : '￥' . min($price);
            $option['price']['text'] = $price_str;
        }

        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['id' => $goods->goods_id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/miaosha/goods/goods'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}