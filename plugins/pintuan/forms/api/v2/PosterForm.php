<?php

namespace app\plugins\pintuan\forms\api\v2;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\pintuan\forms\common\v2\CommonOption;
use app\plugins\pintuan\forms\common\v2\SettingForm;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;

class PosterForm extends GrafikaOption
{
    public $goods_id;
    public $pintuan_group_id;

    private $type = 1;
    private $groupGoodsList = [];

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
        /** @var Goods $goods */
        $goods = Goods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->goods_id])
            ->with('attr', 'goodsWarehouse', 'pintuanGoods')
            ->one();

        if (!$goods) {
            throw new \Exception('拼团商品不存在');
        }

        $goodsIds = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'pintuan_goods_id' => $goods->pintuanGoods->id
        ])->select('goods_id');
        $goodsList = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $goodsIds
        ])->with('attr')->all();

        $this->groupGoodsList = $goodsList;
        return $goods;
    }

    private function get()
    {
        $setting = (new SettingForm)->search();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        /** @var Goods $goods */
        $goods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $goods->coverPic;

        if (isset($option['nickname'])) {
            $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
            if ($this->type == 2) {
                $option['nickname']['text'] .= '邀请您一起拼单';
            }
        }
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        if (isset($option['price'])) {
            $people_num = $min = 0;
            /** @var Goods $item */
            foreach ($this->groupGoodsList as $item) {
                $prices = array_column($item->attr, 'price');
                $min_price = min($prices);
                if ($min_price < $min || $min === 0) {
                    $min = $min_price;
                    $people_num = $item->pintuanGoods->groups->people_num;
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
            ['goods_id' => $goods->id, 'user_id' => \Yii::$app->user->id, 'id' => $this->pintuan_group_id],
            240,
            $this->type == 1 ? 'plugins/pt/goods/goods' : 'plugins/pt/detail/detail'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}