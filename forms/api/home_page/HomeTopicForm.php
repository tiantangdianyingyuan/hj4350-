<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\home_page;


use app\models\Model;
use app\models\Topic;

class HomeTopicForm extends Model
{
    /**
     * @return mixed
     */
    public function getTopics()
    {
        $topics = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_DESC])->limit(10)->asArray()->all();

        $newData = [];
        foreach ($topics as $topic) {
            $arr = [
                'id' => $topic['id'],
                'title' => $topic['title'],
                'icon_url' => $topic['cover_pic'],
            ];
            $newData[] = $arr;
        }

        return $topics;
    }
}
