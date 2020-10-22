<?php

namespace app\plugins\pintuan\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\pintuan\forms\common\CommonOption;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\models\PintuanGoods;

class PosterForm extends GrafikaOption
{
    public $goods_id;
    public $pintuan_group_id;

    private $type = 1;

    public function rules()
    {
        return [
            [['goods_id', 'pintuan_group_id'], 'integer'],
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

    public function orderDetailPoster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->type = 2;
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
        $ptGoods = PintuanGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse', 'goods.attr', 'ptGoodsAttr'])->one();

        if (!$ptGoods) {
            throw new \Exception('拼团商品不存在');
        }
        return $ptGoods;
    }

    private function get()
    {
        $setting = (new SettingForm)->search();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        $goods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $goods->goods->goodsWarehouse->cover_pic;

        if (isset($option['nickname'])) {
            $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
            if ($this->type == 2) {
                $option['nickname']['text'] .= '邀请您一起拼单';
            }
        }
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        if (isset($option['price']) && $goods->ptGoodsAttr) {
            $people_num = $min = 0;
            foreach ($goods->groups as $i) {
                $prices = array_column($i->attr, 'pintuan_price');
                $min_price = min($prices);
                if ($min_price < $min || $min === 0) {
                    $min = $min_price;
                    $people_num = $i->people_num;
                }
            }
            $option['price']['text'] = sprintf('%s人团：%s元', $people_num, $min);
        }

        $cache = $this->getCache(array_merge($option, [
            'pintuan_group_id' => $this->pintuan_group_id,
        ]));
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['goods_id' => $goods->goods_id, 'user_id' => \Yii::$app->user->id, 'id' => $this->pintuan_group_id],
            240,
            $this->type == 1 ? 'plugins/pt/goods/goods' : 'plugins/pt/detail/detail'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}