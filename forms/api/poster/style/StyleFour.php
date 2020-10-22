<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\style;

use app\forms\api\poster\common\StyleGrafika;

class StyleFour extends StyleGrafika implements BaseStyle
{
    use TraitStyle;

    public function build()
    {
        $this->getBg()
            ->getHead()
            ->getImageBg()
            ->getMultiMap(650, 650, 48, 406)
            ->getGoods()
            ->getPrice(48, 288);

        if ($file = $this->setFile($this->taskHash())) {
            return ['pic_url' => $file];
        };
        $this->getDrawing();

        $editor = $this->getPoster($this->poster_arr);
        return ['pic_url' => $editor->qrcode_url];
    }

    protected function getHead()
    {
        $nickname = sprintf('%s%s', \Yii::$app->user->identity->nickname,$this->defaultText['explanation_four']);
        $n = imagettfbbox(24 / self::FONT_FORMAT, 0, $this->font_path, $nickname);
        $n_width = $n[2] - $n[0];

        $this->getRectangle($n_width + 24 * 2
            , 54
            , 140
            , 56
            , '#f1f1f1'
            , [30, ['left-top', 'left-bottom', 'right-top', 'right-bottom']]);

        $user = $this->setText($nickname, 164, 75, 24, '#353535');

        $circle = $this->setEllipse(100, 100, 24, 30, '#FFFFFF');
        array_push($this->poster_arr, $circle, $user);
        return $this;
    }

    protected function getGoods()
    {
        $goodsName = $this->goods->name;
        $goods = $this->setText($goodsName, 48, 160 + 28, 34, '#353535');
        $goods['text'] = self::autowrap($goods['font'], 0, $this->font_path, $goods['text'], 650, 2);

        $remark = $this->setText($this->defaultText['mark_four_text'], 0, 1090 + 150 + 22, 24, '#999999');

        $r = imagettfbbox($remark['font'], 0, $this->font_path, $remark['text']);
        $r_width = $r[2] - $r[0];
        $remark['left'] = (750 - $r_width) / 2;

        array_push($this->poster_arr
            , $goods
            , $remark
        );
        return $this;
    }

    protected function getImageBg()
    {
        $image_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/icon/four-bg.png';
        array_push($this->poster_arr
            , $this->setImage($image_path, 700, 1150, 24, 160)
        );
        return $this;
    }

    protected function getDrawing()
    {
        array_push($this->poster_arr
            , $this->setImage(self::head($this), 90, 90, 24 + 5, 30 + 5)
            , $this->setImage($this->takeQrcode($this), 150, 150, 300, 1090)
        );
        return $this;
    }
}