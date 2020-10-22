<?php

namespace app\forms\api\poster;

use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\models\Option;
use app\models\UserInfo;

class SharePosterForm extends GrafikaOption implements BasePoster
{
    public function get()
    {
        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['share'];
        $option = CommonOption::get(Option::NAME_POSTER, \Yii::$app->mall->id, Option::GROUP_APP)['share'];

        $option = $this->optionDiff($option, $default);

        isset($option['name']) && $option['name']['text'] = \Yii::$app->user->identity->nickname;
        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['user_id' => \Yii::$app->user->id],
            240,
            'pages/index/index'
        ], $this);
        isset($option['head']) && $option['head']['file_path'] = self::head($this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}