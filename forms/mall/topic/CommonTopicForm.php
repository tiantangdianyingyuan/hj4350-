<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\topic;

use app\models\Model;
use app\models\Topic;
use app\models\TopicType;

class CommonTopicForm extends Model
{
    public $keyword;
    public $type;
    public $page_size;
    public $page;
    public $topics_ids;

    public function getList()
    {
        $query = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->with('topicType');

        if ($this->keyword) {
            $query->keyword($this->keyword, ['like', 'title', $this->keyword]);
        }

        $list = $query->keyword($this->type, ['type' => $this->type])
            ->keyword($this->topics_ids, ['id' => $this->topics_ids])
            ->page($pagination, $this->page_size, $this->page)
            ->orderBy('sort ASC,created_at DESC')
            ->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * @param Topic[] $list
     * @throws \Exception
     * @return array
     */
    public function resetTopicList($list)
    {
        if (!is_array($list)) {
            throw new \Exception('传入的参数不是数组');
        }
        $newList = [];
        foreach ($list as $topic) {
            if (!($topic instanceof Topic)) {
                throw new \Exception('无效的参数');
            }
            $readCount = price_format($topic->read_count + $topic->virtual_read_count, 'string', 0);
            $newItem = [
                'id' => $topic->id,
                'title' => $topic->title,
                'cover_pic' => $topic->cover_pic,
                'read_count' => ($readCount > 10000 ? intval($readCount / 10000) . '万+' : $readCount)  . '人浏览',
                'layout' => $topic->layout,
                'pic_list' => $topic->pic_list ? \Yii::$app->serializer->decode($topic->pic_list) : [],
                'abstract' => $topic->abstract,
            ];
            $newList[] = $newItem;
        }
        return $newList;
    }

    /**
     * @return array
     */
    public function getListByType()
    {
        $list = TopicType::find()->with(['topics' => function ($query) {
            $query->andwhere(['is_delete' => 0])
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC]);
        }])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1])
            ->keyword($this->type, ['id' => $this->type])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])
            ->page($pagination, $this->page_size, $this->page)
            ->all();
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
