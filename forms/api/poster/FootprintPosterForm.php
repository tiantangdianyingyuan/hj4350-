<?php


namespace app\forms\api\poster;

use app\forms\common\grafika\GrafikaOption;
use app\models\FootprintDataLog;

class FootprintPosterForm extends GrafikaOption implements BasePoster
{
    public function rules()
    {
        return [
        ];
    }

    public function get()
    {
        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['footprint'];
        $option = $default;

        $list = FootprintDataLog::find()
            ->where(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere(['>', 'statistics_time', date('Y-m-d 00:00:00', time())])
            ->asArray()
            ->all();
        if (!$list) {
            throw new \Exception('数据异常');
        }
        $new_list = [];
        foreach ($list as $value) {
            $new_list[$value['key']] = $value['value'];
        }

        isset($option['text_2']) && $option['text_2']['text'] = $new_list['pay_num'];
        $text = imagettfbbox($option['text_2']['font'], 0, $this->font_path, $option['text_2']['text']);
        isset($option['text_3']) && $option['text_3']['left'] = ($text[2] - $text[1]) + $option['text_2']['left'];
        isset($option['text_6']) && $option['text_6']['text'] = $new_list['pay_price'];
        isset($option['text_9']) && $option['text_9']['text'] = $new_list['highest_price'];

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['user_id' => \Yii::$app->user->id],
            240,
            'pages/foot/index/index'
        ], $this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}