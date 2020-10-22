<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\poster;

use app\forms\api\poster\parts\PosterBg;
use app\models\Goods;
use app\models\UserInfo;

trait PosterConfigTrait
{
    public function getAll(): array
    {
        return array_merge($this->getGoods()
            , $this->getUser()
            , $this->getQrcode()
            , $this->getExtra()
            , $this->getPlugin()
        );
    }

    public function getPlugin(): array
    {
        return [
            'sign' => ''
        ];
    }

    public function getUser(): array
    {
        $user = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        $default = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/user-default-avatar.png';
        return [
            'nickname' => \Yii::$app->user->identity->nickname,
            'avatar' => $user->avatar ?: $default,
        ];
    }

    public function getQrcode(): array
    {
        switch (\Yii::$app->appPlatform) {
            case APP_PLATFORM_WXAPP:
                $qrcodeUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster/default_wxapp_qr_code.png';
                break;
            case APP_PLATFORM_ALIAPP:
                $qrcodeUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster/default_aliapp_qr_code.png';
                break;
            case APP_PLATFORM_TTAPP:
                $qrcodeUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster/default_ttapp_qr_code.png';
                break;
            case APP_PLATFORM_BDAPP:
                $qrcodeUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster/default_bdapp_qr_code.png';
                break;
            default:
                $qrcodeUrl = '';
                break;
        }
        return [
            'qrcode_url' => $qrcodeUrl,
        ];
    }

    public function getConfig(): array
    {
        define('GOODS', 'goods');
        $data = PosterConfigForm::get();
        return array_merge($data[GOODS], ['color' => PosterBg::COLOR_LIST]);
    }

    public function getExtra(): array
    {
        /** plugin */
        return [];
    }

    public function getGoods(): array
    {
        $goods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->goods_id,
        ])->with(['attr', 'mallGoods'])->one();
        if (empty($goods)) {
            throw new \Exception('海报商品不存在');
        }

        $prices = array_column($goods->attr, 'price');
        if (empty($prices)) {
            throw new \Exception('海报-规格数据异常');
        }

        $picUrl = \yii\helpers\BaseJson::decode($goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }

        return [
            'goods_name' => $goods->name,
            'is_negotiable' => $goods->mallGoods->is_negotiable ?? 0,
            'min_price' => min($prices),
            'max_price' => max($prices),
            'multi_map' => $pic_list,
        ];
    }

    public function formatType(array $data)
    {
        $keys = ['top', 'left', 'height', 'width', 'font', 'right', 'bottom', 'two_image_height'];
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->formatType($item);
                continue;
            }

            if (in_array($key, $keys)) {
                if (!preg_match('/(\d+)%/', $item, $matches)) {
                    $data[$key] = $item . 'rpx';
                }
            }
            if($key === 'font') {
                $data['font-size']  = $data[$key];
                unset($data['font']);
            }
            if ($key === 'two_image_url') {
                $data['image_url'] = $item;
                unset($data['two_image_url']);
            }
            if ($key === 'two_image_height') {
                $data['height'] = $item . 'rpx';
                unset($data['two_image_height']);
            }
        }
        return $data;
    }
}