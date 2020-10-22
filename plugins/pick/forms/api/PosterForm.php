<?php

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\pick\forms\common\CommonOption;
use app\plugins\pick\models\PickGoods;
use app\plugins\pick\models\PickSetting;

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
        $pickGoods = PickGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse'])->one();
        if (!$pickGoods) {
            throw new \Exception('N元任选商品不存在');
        }
        return $pickGoods;
    }

    public function get()
    {
        $setting = PickSetting::getList(\Yii::$app->mall->id);
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        $pickGoods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $pickGoods->goods->goodsWarehouse->cover_pic;
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['price']) && $option['price']['text'] = $pickGoods->goods->price;
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $pickGoods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], 750 - $option['desc']['left'], 2);

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['goods_id' => $pickGoods->goods_id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/pick/goods/goods'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}