<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\api;


use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\quick_share\forms\common\CommonGoods;
use app\plugins\quick_share\forms\common\CommonPoster;
use app\plugins\quick_share\models\Goods;
use Grafika\Grafika;

class GoodsPoster extends GrafikaOption
{
    public $goods_id;
    public $id;

    public function rules()
    {
        return [
            [['goods_id', 'id'], 'integer'],
        ];
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->goods_id) {
                $query = Goods::find()->select('g.*')->alias('g')->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->goods_id
                ]);
                $goods = $query->one();
                $quickShareGoods = $goods->quickShareGoods;
                if ($quickShareGoods) {
                    $share_pic = \yii\helpers\BaseJson::decode($quickShareGoods->share_pic);
                } else {
                    $share_pic = \yii\helpers\BaseJson::decode($goods->goodsWarehouse->pic_url);
                }
                $list = $query->addSelect(["total_sales" => "`g`.`sales` + `g`.`virtual_sales`"])->asArray()->one();

                $data = $this->getGoodsPoster($goods, array_pop($share_pic), $list['total_sales']);
            } else {
                $goods_id = null;
                $model = CommonGoods::getGoods($this->id, $goods_id, 1);
                if ($model->goods_id) {
                    $query = Goods::find()->select('g.*')->alias('g')->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'id' => $model->goods->id
                    ]);
                    $list = $query->addSelect(["total_sales" => "`g`.`sales` + `g`.`virtual_sales`"])->asArray()->one();
                    $share_pic = \yii\helpers\BaseJson::decode($model->share_pic);

                    $data = $this->getGoodsPoster($model->goods, array_pop($share_pic), $list['total_sales']);
                } else {
                    $share_pic = \yii\helpers\BaseJson::decode($model->share_pic);
                    $data = $this->getDynamic(array_pop($share_pic));
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function getGoodsPoster($goods, $share_pic, $sales = 0)
    {
        $option = $this->optionDiff(CommonPoster::getGoods(), CommonPoster::getGoods());
        isset($option['pic']) && $option['pic']['file_path'] = $share_pic['pic_url'];
        if (isset($option['name'])) {
            $name = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goodsWarehouse->name, 750 - 2 * $option['name']['left'], 2);
            $name_arr = explode("\n", $name);
            if (count($name_arr) > 1) {
                $option['name']['text'] = $name_arr[0];
                $option['name_two']['text'] = $name_arr[1];
            } else {
                $option['name']['text'] = $name;
                unset($option['name_two']);
            }
        }
        isset($option['sales']) && $option['sales']['text'] = sprintf('销量%s%s', $sales, $goods->unit);

        if (isset($option['price'])) {
            $price = array_column($goods->attr, 'price');
            if ($goods->mallGoods['is_negotiable']) {
                $price_str = '价格面议';
                unset($option['price_desc']);
            } else {
                $price_str = min($price);
                $price_str = max($price) > min($price) ? min($price) . '~' . max($price) : min($price);
            }
            $option['price']['font_path'] = \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf';
            $option['price']['text'] = self::autowrap($option['price']['font'], 0, $this->font_path, $price_str, 750 - 2 * $option['price']['left'], 1);;
        }

        if (isset($option['original_price']) && isset($option['price'])) {
            $option['original_price']['text'] = '￥' . $goods->goodsWarehouse->original_price;
            $text = imagettfbbox($option['price']['font'], 0, $this->font_path, $option['price']['text']);
            $option['original_price']['left'] = $text[2] - $text[0] + $option['original_price']['left'] + $option['price']['left'] - 8;

            $original_text = imagettfbbox($option['original_price']['font'], 0, $this->font_path, $option['original_price']['text']);
            $option['line'] = [
                'is_show' => '1',
                'width' => $original_text[2] - $original_text[0] + 10,
                'height' => 1,
                'top' => $option['original_price']['top'] + ($original_text[1] - $original_text[7]) / 2,
                'left' => $option['original_price']['left'],
                'color' => '#999999',
                'file_type' => 'line',
            ];
            $cache = $this->getCache($option);
            if ($cache) {
                return ['pic_url' => $cache];
            }
            $params = [
                ['id' => $goods->id, 'user_id' => \Yii::$app->user->id],
                240,
                'pages/goods/goods'
            ];
            if (isset($option['qr_code'])) {
                if (\Yii::$app->appPlatform === APP_PLATFORM_BDAPP) {
                    unset($option['qr_code']);
                } else {
                    $option['qr_code']['file_path'] = self::qrcode($option, $params, $this);
                }
            }
            isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, $params, $this);
            $editor = $this->getPoster($option);
        }

        return ['pic_url' => $editor->qrcode_url];
    }

    public function getDynamic($share_pic)
    {
        $option = $this->optionDiff(CommonPoster::getDynamic(), CommonPoster::getDynamic());
        isset($option['pic']) && $option['pic']['file_path'] = $share_pic['pic_url'];
        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache];
        }
        $params = [
            ['user_id' => \Yii::$app->user->id],
            240,
            'pages/index/index'
        ];

        if (isset($option['qr_code'])) {
            if (\Yii::$app->appPlatform === APP_PLATFORM_BDAPP) {
                unset($option['qr_code']);
            } else {
                $option['qr_code']['file_path'] = self::qrcode($option, $params, $this);
            }
        }
        $editor = $this->getBoxPoster($option);
        return ['pic_url' => $editor->qrcode_url];
    }

    public function getBoxPoster(array $option)
    {
        foreach ($option as $k => $v) {
            if ($k == 'bg_pic') {
                if ($this->isUrl($option[$k]['url'])) {
                    $option[$k]['url'] = self::saveTempImage($this->destroyList($option[$k]['url']));
                }
                $this->model->open($goods_qrcode, $option[$k]['url']);
                $this->model->resizeExact($goods_qrcode, 750, 1054);
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'image') {
                if (array_key_exists('size', $option[$k])) {
                    $option[$k]['width'] = $option[$k]['size'];
                    $option[$k]['height'] = $option[$k]['size'];
                }
                if ($this->isUrl($option[$k]['file_path'])) {
                    $option[$k]['file_path'] = self::saveTempImage($this->destroyList($option[$k]['file_path']));
                }
                $this->apiBlend($goods_qrcode, $xx, $option[$k]['file_path'], $option[$k]['width'], $option[$k]['height'], 'normal', 1, 'top-left', $option[$k]['left'], $option[$k]['top']);
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'text') {
                $this->apiText($goods_qrcode, $option[$k]['text'], $option[$k]['font'], $option[$k]['left'], $option[$k]['top'], $option[$k]['color']);
            }
            if (array_key_exists('file_type', $v) && $v['file_type'] == 'line') {
                $this->model->draw($goods_qrcode, Grafika::createDrawingObject('Rectangle', $option[$k]['width'], $option[$k]['height'], array($option[$k]['left'], $option[$k]['top']), 0, null, new Color($option[$k]['color'])));
            }
        }
        $this->apiSave($goods_qrcode);
        return $this;
    }
}