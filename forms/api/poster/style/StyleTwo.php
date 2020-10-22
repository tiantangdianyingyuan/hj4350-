<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\style;

use app\forms\api\poster\common\StyleGrafika;

class StyleTwo extends StyleGrafika implements BaseStyle
{
    use TraitStyle;

    public function build()
    {
        $this->getBg()
            ->getMultiMap(750, 750, 0, 0)
            ->getEndBg()
            ->getPrice(52, 846)
            ->getOther()
            ->getRemark();

        if ($file = $this->setFile($this->taskHash())) {
            return ['pic_url' => $file];
        };

        $this->getDrawing();
        $editor = $this->getPoster($this->poster_arr);
        return ['pic_url' => $editor->qrcode_url];
    }

    protected function getEndBg()
    {
        $image_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/icon/style-two-end-shadow.png';
        $arr = $this->setImage($image_path, 702, 600, 24, 710 - 16);
        array_push($this->poster_arr, $arr);
        return $this;
    }


    protected function getOther()
    {
        $goodsName = $this->setText($this->goods['name'], 48, 750, 34, '#353535');
        $goodsName['text'] = self::autowrap($goodsName['font'], 0, $this->font_path, $goodsName['text'], 702 - 24 - 24, 2);

        $userName = $this->setText(\Yii::$app->user->identity->nickname, 54 + 24 + 96 + 26, 1124, 28, '#353535');
        if ($this->defaultText['end_two_remark']) {
            $userName['top'] = $userName['top'] - 8;
            $remarkText = $this->setText($this->defaultText['end_two_remark'], 54 + 24 + 96 + 26, 1124 + 20, 24, '#353535');

            array_push($this->poster_arr, $remarkText);
        }
        $userName['text'] = self::autowrap($userName['font'], 0, $this->font_path, $userName['text'], 200, 1);

        array_push($this->poster_arr, $goodsName, $userName);
        return $this;
    }

    protected function getRemark()
    {
        $remarkText = $this->setText($this->defaultText['mark_two_text'], 24 + 54 + 20, 1218, 24, '#353535');

        $image_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/icon/three-arrow.png';
        $iconRemark = $this->setImage($image_path, 15, 17, 310 + 54 + 24 - 20 - 40, 1218);

        $this->getRectangle(280
            , 52
            , 54 + 24
            , 1200
            , '#f1f1f1'
            , [30, ['left-top', 'left-bottom', 'right-top', 'right-bottom']]
        );
        array_push($this->poster_arr, $remarkText, $iconRemark);
        return $this;
    }

    protected function getDrawing()
    {
        array_push($this->poster_arr
            , $this->setImage(self::head($this), 96, 96, 54 + 24, 1090)
            , $this->setImage($this->takeQrcode($this), 230, 230, 455, 1052)
        );
        return $this;
    }
}