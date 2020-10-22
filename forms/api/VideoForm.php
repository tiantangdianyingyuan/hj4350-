<?php
namespace app\forms\api;

use app\models\Model;
use app\models\Video;
use app\core\response\ApiCode;

class VideoForm extends Model
{
    public $limit;

    public function rules()
    {
        return [
            [['limit'], 'integer',],
            [['limit',], 'default', 'value' => 10],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $list = Video::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])
            ->orderBy('sort ASC, ID DESC')
            ->page($pagination, $this->limit)
            ->asArray()
            ->all();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('m月d日', strtotime($v['created_at']));
            $list[$k]['url'] = \app\forms\common\video\Video::getUrl($v['url']);
        };
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }
}
