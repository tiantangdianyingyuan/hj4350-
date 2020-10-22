<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/26
 * Time: 18:16
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\models\UserInfo;
use app\plugins\advance\forms\common\CommonOption;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;

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
        $advanceGoods = AdvanceGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with(['goods.goodsWarehouse'])->one();
        if (!$advanceGoods) {
            throw new \Exception('预售商品不存在');
        }
        return $advanceGoods;
    }

    private function get()
    {
        $setting = (new SettingForm())->search();
        $option = $this->optionDiff($setting['goods_poster'], CommonOption::getPosterDefault());
        $goods = $this->getGoods();

        //分享
        if (isset($option['pic'])) {
            $option['pic']['file_path'] = $goods->goods->goodsWarehouse->cover_pic;
        }

        if (isset($option['nickname'])) {
            $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        }

        if (isset($option['name'])) {
            $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goods->goodsWarehouse->name, 750 - $option['name']['left'], 2);
        }

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

        if (isset($option['qr_code'])) {
            $code = (new CommonQrCode())->getQrCode(['id' => $goods->goods_id, 'user_id' => \Yii::$app->user->id], 240, 'plugins/advance/detail/detail');
            $code_path = self::saveTempImage($code['file_path']);
            if ($option['qr_code']['type'] == 1) {
                $code_path = self::avatar($code_path, $this->temp_path, $option['qr_code']['size'], $option['qr_code']['size']);
            }
            $option['qr_code']['file_path'] = $this->destroyList($code_path);
        }

        if (isset($option['head'])) {
            $user = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
            $avatar = self::avatar(self::saveTempImage($user->avatar), $this->temp_path, 0, 0);
            $option['head']['file_path'] = $this->destroyList($avatar);
        }

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}