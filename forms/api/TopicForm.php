<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\video\Video;
use app\models\Model;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\TopicType;
use app\utils\GetInfo;
use yii\helpers\ArrayHelper;

class TopicForm extends Model
{
    public $page;
    public $limit;
    public $id;
    public $type;
    public $is_favorite;

    public function rules()
    {
        return [
            [['id', 'limit', 'type'], 'integer',],
            [['limit',], 'default', 'value' => 10],
            [['is_favorite'], 'string'],
        ];
    }

    public function type()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $list = TopicType::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1
        ])->orderBy('sort ASC,id DESC')
            ->asArray()
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $query = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if ($this->type == '-1') {
            $query = $query->andWhere(['is_chosen' => 1]);
        } elseif ($this->type) {
            $query = $query->andWhere(['type' => $this->type]);
        }
        $list = $query->orderBy("sort ASC, id DESC")->page($pagination, $this->limit)->all();

        array_walk($list, function (&$item) {
            $item = $item->toArray();
            $read_count = intval($item['read_count'] + $item['virtual_read_count']);
            $goods_class = 'class="goods-link"';
            $goods_count = mb_substr_count($item['content'], $goods_class);
            $item['read_count'] = $read_count < 10000 ? $read_count . '人浏览' : intval($read_count / 10000) . '万+人浏览';
            $item['goods_count'] = $goods_count ? $goods_count . '件宝贝' : '';
            $item['pic_list'] = $item['pic_list'] ? \Yii::$app->serializer->decode($item['pic_list']) : [];
            unset($item['content']);
        });
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->with(['favorite' => function ($query) {
            try {
                $user_id = \Yii::$app->user->identity->id;
            } catch (\Exception $e) {
                $user_id = 0;
            }
            $query->where(['user_id' => $user_id, 'is_delete' => 0]);
        }]);

        $list = $query->one();
        if (!$list) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '文章不存在',
            ];
        }
        $list->read_count++;
        $list->save();
        $favorite = (!\Yii::$app->user->isGuest && $list->favorite) ? ArrayHelper::toArray($list->favorite) : [];
        $list = ArrayHelper::toArray($list);

        $read_count = intval($list['read_count'] + $list['virtual_read_count']);
        $goods_class = 'class="goods-link"';
        $goods_count = mb_substr_count($list['content'], $goods_class);
        $list['read_count'] = $read_count < 10000 ? $read_count . '人浏览' : intval($read_count / 10000) . '万+人浏览';
        $list['goods_count'] = $goods_count ? $goods_count . '件宝贝' : '';
        $list['content'] = $this->transTxvideo($list['content']);
        $list['is_favorite'] = count($favorite) == 0 ? 'no_love' : 'love';
        if ($list['detail']) {
            $list['detail'] = \Yii::$app->serializer->decode($list['detail']);
            foreach ($list['detail'] as &$item) {
                if ($item['id'] == 'video') {
                    $item['data']['url'] = Video::getUrl($item['data']['url']);
                }
            }
            unset($item);
        } else {
            $list['detail'] = [
                [
                    'id' => 'image-text',
                    'data' => [
                        'content' => $list['content']
                    ]
                ]
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function favorite()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $form = TopicFavorite::findOne([
            'user_id' => \Yii::$app->user->identity->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'topic_id' => $this->id,
        ]);

        if ($this->is_favorite == 'no_love' && $form) {
            $form->is_delete = 1;
            $form->deleted_at = date('Y-m-d');
            $form->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功'
            ];
        }

        if ($this->is_favorite == 'love') {
            if ($form) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '收藏成功'
                ];
            }

            $favorite = new TopicFavorite();
            $favorite->mall_id = \Yii::$app->mall->id;
            $favorite->user_id = \Yii::$app->user->identity->id;
            $favorite->topic_id = $this->id;
            if ($favorite->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '收藏成功',
                ];
            }
            return $this->getErrorResponse($favorite);
        }
    }

    private function transTxvideo($content)
    {
        preg_match_all("/https\:\/\/v\.qq\.com[^ '\"]+\.html/i", $content, $match_list);
        if (!is_array($match_list) || count($match_list) == 0) {
            return $content;
        }
        $url_list = $match_list[0];
        foreach ($url_list as $url) {
            $res = GetInfo::getVideoInfo($url);
            if ($res['code'] == 0) {
                $new_url = $res['url'];
                $content = str_replace('src="' . $url . '"', 'src="' . $new_url . '"', $content);
            }
        }
        return $content;
    }
}
