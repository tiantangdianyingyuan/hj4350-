<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\style;

use app\forms\api\poster\common\StyleGrafika;

class StyleThree extends StyleGrafika implements BaseStyle
{
    use TraitStyle;

    public function build()
    {
        $this->getBg()
            ->getMultiMap(680, 680, 35, 175)
            ->getNickname()
            ->getGoodsName()
            ->getPrice(0, 957, true, $this->color === '#fc4a3b' ? '#353535' : '#ff4544');
        if ($file = $this->setFile($this->taskHash())) {
            return ['pic_url' => $file];
        };
        $this->getDrawing();
        $editor = $this->getPoster($this->poster_arr);
        return ['pic_url' => $editor->qrcode_url];
    }

    protected function getNickname(): StyleThree
    {
        $image_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/icon/three-love.png';
        array_push($this->poster_arr
            , $this->setText('我看上了这款商品', 162, 46, 26, $this->color === '#000000' ? '#d9d9d9' : '#353535')
            , $this->setText($this->defaultText['head_three_text'], 162, 82, 28, $this->color === '#000000' ? '#d9d9d9' : '#353535')
            , $this->setText('比心~', 162, 116, 28, $this->color === '#000000' ? '#d9d9d9' : '#353535')
            , $this->setImage($image_path, 24, 24, 230, 115)
            , $this->setLine([24, 1052], [750 - 24, 1052], '#c9c9c9')
            , $this->setText($this->defaultText['mark_three_text'], 318, 1182, 28, $this->color === '#000000' ? '#d9d9d9' : '#353535')
        );
        return $this;
    }

    protected function getGoodsName(): StyleThree
    {
        $goodsName = $this->goods->name;
        $goods = $this->setText($goodsName, 0, 175 + 28 + 680, 34, $this->color === '#000000' ? '#d9d9d9' : '#353535');
        $goods['text'] = self::autowrap($goods['font'], 0, $this->font_path, $goods['text'], 660, 1);

        $g = imagettfbbox($goods['font'], 0, $this->font_path, $goods['text']);
        $g_width = $g[2] - $g[0];
        $goods['left'] = (750 - $g_width) / 2;

        array_push($this->poster_arr
            , $goods
        );
        return $this;
    }


    protected function getDrawing(): StyleThree
    {
        array_push($this->poster_arr
            , $this->setImage(self::head($this), 97, 97, 35, 40)
            , $this->setImage($this->takeQrcode($this), 230, 230, 50, 1080)
        );
        return $this;
    }
}