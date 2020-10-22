<?php

namespace app\plugins\bargain\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\bargain\forms\common\CommonSetting;
use app\plugins\bargain\models\BargainGoods;

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
        $bargainGoods = BargainGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse'])->one();
        if (!$bargainGoods) {
            throw new \Exception('砍价商品不存在');
        }
        return $bargainGoods;
    }

    public function get()
    {
        $setting = CommonSetting::getCommon(\Yii::$app->mall)->getList();
        $option = $this->optionDiff($setting['goods_poster'], CommonSetting::getPosterDefault());
        $bargainGoods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $bargainGoods->goods->goodsWarehouse->cover_pic;
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['price']) && $option['price']['text'] = sprintf('最低￥%s', $bargainGoods->min_price);
        isset($option['time_str']) && $option['time_str']['text'] = date('m.d H:i', strtotime($bargainGoods->end_time));
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $bargainGoods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['goods_id' => $bargainGoods->goods_id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/bargain/goods/goods'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}