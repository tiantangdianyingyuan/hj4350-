<?php

namespace app\forms\api\poster;

use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\forms\common\grafika\GrafikaOption;
use app\models\Option;
use app\models\Topic;

class TopicPosterForm extends GrafikaOption implements BasePoster
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    public function get()
    {
        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['topic'];
        $option = CommonOption::get(Option::NAME_POSTER, \Yii::$app->mall->id, Option::GROUP_APP)['topic'];
        $option = $this->optionDiff($option, $default);

        $topic = Topic::findOne($this->id);
        if (!$topic) {
            throw new \Exception('专题不存在');
        }

        isset($option['pic']) && $option['pic']['file_path'] = $topic['cover_pic'];
        isset($option['title']) && $option['title']['text'] = self::autowrap($option['title']['font'], 0, $this->font_path, $topic['title'], 670, 1);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        if (isset($option['look'])) {
            $read = $topic['virtual_read_count'] + $topic['read_count'];
            $read = $read > 10000 ? ($read / 10000) . '万+人浏览' : $read . '人浏览';
            $option['look']['text'] = $read;
        }

        isset($option['content']) && $option['content']['text'] = self::autowrap($option['content']['font'], 0, $this->font_path, $topic['abstract'], 670, 2);

        if (isset($option['open_desc'])) {
            $text = imagettfbbox($option['open_desc']['font'], 0, $this->font_path, $option['open_desc']['text']);
            $option['icon'] = [
                'is_show' => '1',
                'size' => 24,
                'left' => ($text[2] - $text[1]) / 2 + $option['open_desc']['left'],
                'top' => $option['open_desc']['top'] + 40,
                'file_path' => \Yii::$app->basePath . '/web/statics/img/mall/topic-hb-down.png',
                'file_type' => 'image',
            ];
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }

        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            ['id' => $topic->id, 'user_id' => \Yii::$app->user->id],
            240,
            'pages/topic/topic'
        ], $this);

        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}