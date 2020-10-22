<?php


namespace app\plugins\quick_share\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\quick_share\forms\common\CommonGoods;
use app\plugins\quick_share\forms\common\CommonPoster;
use app\plugins\quick_share\forms\common\CommonQuickShare;
use app\plugins\quick_share\models\Goods;

class PosterForm extends GrafikaOption
{
    public $goods_id;
    public $id;

    public function rules()
    {
        return [
            [['goods_id', 'id'], 'integer'],
            [['goods_id', 'id'], 'eitherOneRequired', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function eitherOneRequired($attribute, $params, $validator)
    {
        if (empty($this->goods_id)
            && empty($this->id)
        ) {
            $this->addError($attribute, '参数不能为空');
            return false;
        }
        return true;
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

    private function getGoods($goods_id)
    {
        $goods = Goods::findOne([
            'is_delete' => 0,
            'id' => $goods_id,
        ]);
        if ($goods) return $goods;
        throw new \Exception('商品不存在或已删除');
    }

    private function get()
    {
        $setting = CommonQuickShare::getSetting();
        $option = $this->optionDiff($setting['goods_poster'], CommonPoster::getPosterDefault());

        if ($this->id) {
            $share_goods = CommonGoods::getGoods($this->id);
            $mall_goods = $share_goods->goods;
        }
        if ($this->goods_id) {
            $goods = $this->getGoods($this->goods_id);
            $mall_goods = $goods;
            $share_goods = $goods->quickShareGoods;
        }

        if (isset($option['price']) && isset($mall_goods)) {
            $price = array_column($mall_goods->attr, 'price');
            $price_str = $mall_goods->mallGoods['is_negotiable'] ? '价格面议' : (max($price) > min($price) ? '￥' . min($price) . '~' . max($price) : '￥' . min($price));
            $option['price']['text'] = $price_str;
        }

        if (isset($option['pic'])) {
            if (isset($share_goods)) {
                $share_pic = \yii\helpers\BaseJson::decode($share_goods->share_pic);
                $file_path = array_shift($share_pic)['pic_url'];
            } else {
                $file_path = $goods->goodsWarehouse->cover_pic;
            }
            $option['pic']['file_path'] = $file_path;
        }

        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, isset($share_goods) ? $share_goods->share_text : $goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);
        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache];
        }

        if (isset($mall_goods)) {
            $params = [
                ['id' => $mall_goods->id, 'user_id' => \Yii::$app->user->id],
                240,
                'pages/goods/goods'
            ];
        } else {
            $params = [
                ['user_id' => \Yii::$app->user->id],
                240,
                'pages/index/index'
            ];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, $params, $this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url];
    }
}