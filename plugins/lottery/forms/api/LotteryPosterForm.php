<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\lottery\forms\common\CommonLottery;
use app\plugins\lottery\forms\common\CommonOption;
use app\plugins\lottery\models\Lottery;

class LotteryPosterForm extends GrafikaOption
{
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
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
        $goods = Lottery::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods_id,
            'is_delete' => 0,
        ])->with('goods.goodsWarehouse')->one();
        if (!$goods) {
            throw new \Exception('商品不能为空');
        }
        return $goods;
    }

    private function get()
    {
        $setting = CommonLottery::getSetting();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        $goods = $this->getGoods();

        isset($option['pic']) && $option['pic']['file_path'] = $goods->goods->goodsWarehouse->cover_pic;
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;

        if (isset($option['price'])) {
            $option['price']['text'] = sprintf("￥%s", $goods->goods->price);

            $width = imagettfbbox($option['price']['font'], 0, $this->font_path, $option['price']['text']);
            if ($option['price']['del_line']) {
                $option['line_url']['top'] = ($width[1] - $width[7]) / 2 + $option['price']['top'];
                $option['line_url']['left'] = $option['price']['left'];
                $option['line_url']['color'] = $option['price']['color'];
                $option['line_url']['width'] = $width[2] + $width[0];
                $option['line_url']['file_type'] = 'line';
            } else {
                unset($option['line_url']);
            }
        }

        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        $cache = $this->getCache(array_merge($option, [
            'id' => $goods->id,
        ]));
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['lottery_id' => $goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/lottery/goods/goods'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}
