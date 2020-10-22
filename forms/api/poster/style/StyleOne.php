<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\poster\style;

use app\forms\api\poster\common\StyleGrafika;

class StyleOne extends StyleGrafika implements BaseStyle
{
    use TraitStyle;

    public function build()
    {
        $this->getBg()
            ->getMultiMap(702, 702, 24, 225, [16, ['left-top', 'right-top']])
            ->getNickname()
            ->getEnd()
            ->getPrice(52, 1050);

        if ($file = $this->setFile($this->taskHash())) {
            return ['pic_url' => $file . '?v=' . time()];
        };

        $this->getDrawing();
        $editor = $this->getPoster($this->poster_arr);
        return ['pic_url' => $editor->qrcode_url];
    }

    protected function getNickname(): StyleOne
    {
        $user = sprintf('%s%s', \Yii::$app->user->identity->nickname, $this->defaultText['explanation_one']);
        $userArr = $this->setText($user, 160, 138, 24, '#000000');
        $nameSize = imagettfbbox($userArr['font'], 0, $this->font_path, $userArr['text']);

        $u_width = $nameSize[2] - $nameSize[0];
        $padding = 24 + 24;

        $this->getRectangle($u_width + $padding
            , 54
            , 160 - 24
            , 119
            , '#f1f1f1'
            , [30, ['left-top', 'left-bottom', 'right-top', 'right-bottom']]);

        array_push($this->poster_arr
            , $userArr
        );
        return $this;
    }

    protected function getEnd(): StyleOne
    {
        $goods_bg_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/icon/one-bg.png';
        $goodsBg = $this->setImage($goods_bg_path, 702, 311, 24, 702 + 225);

        $goodsName = $this->setText($this->goods->name ?? '', 24 + 28, 225 + 702 + 28, 36, '#000000');
        $maxWidth = 400;
        $limit = 2;
        $goodsName['text'] = self::autowrap($goodsName['font'], 0, $this->font_path, $goodsName['text'], $maxWidth, $limit);

        array_push($this->poster_arr
            , $goodsBg
            , $goodsName
            , $this->setText($this->defaultText['mark_one_text'], 24 + 28, 1168, 28, '#999999')
        );
        return $this;
    }

    protected function getDrawing(): StyleOne
    {
        array_push($this->poster_arr
            , $this->setImage(self::head($this), 90, 90, 24, 96)
            , $this->setImage($this->takeQrcode($this), 230, 230, 750 - 24 - 28 - 230, 225 + 702 + 28)
        );
        return $this;
    }
}