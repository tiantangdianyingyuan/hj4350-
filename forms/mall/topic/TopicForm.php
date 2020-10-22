<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\topic;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Topic;
use app\models\TopicType;
use Yii;
use yii\helpers\ArrayHelper;

class TopicForm extends Model
{
    public $model;
    public $page;
    public $page_size;

    public $id;
    public $mall_id;
    public $type;
    public $title;
    public $sub_title;
    public $content;
    public $layout;
    public $sort;
    public $cover_pic;
    public $read_count;
    public $agree_count;
    public $virtual_read_count;
    public $virtual_agree_count;
    public $virtual_favorite_count;
    public $is_chosen;
    public $is_delete;
    public $qrcode_pic;
    public $app_share_title;
    public $search;
    public $pic_list;
    public $detail;
    public $abstract;

    public function rules()
    {
        return [
            [['type', 'layout', 'sort', 'read_count', 'agree_count', 'virtual_read_count', 'virtual_agree_count',
                'virtual_favorite_count', 'is_chosen', 'is_delete', 'id'], 'integer'],
            [['content', 'app_share_title', 'search', 'abstract'], 'string'],
            [['title', 'sub_title', 'cover_pic', 'qrcode_pic'], 'string', 'max' => 255],
            [['is_delete', 'virtual_read_count', 'read_count', 'agree_count', 'virtual_agree_count', 'layout', 'sort',
                'virtual_favorite_count', 'is_chosen'], 'default', 'value' => 0],
            [['sub_title'], 'default', 'value' => ''],
            [['sort'], 'integer', 'min' => 0, 'max' => 999999999],
            [['page',], 'default', 'value' => 1],
            [['page_size'], 'default', 'value' => 10],
            [['pic_list', 'detail'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'type' => '分类',
            'title' => '名称',
            'sub_title' => '副标题',
            'content' => '专题内容',
            'layout' => '布局方式：0=小图，1=大图模式 2=多图模式',
            'sort' => '排序：升序',
            'cover_pic' => '封面图',
            'read_count' => '阅读量',
            'agree_count' => '点赞数',
            'virtual_read_count' => '虚拟阅读量',
            'virtual_agree_count' => '虚拟点赞数',
            'virtual_favorite_count' => '虚拟收藏量',
            'is_chosen' => '精选',
            'is_delete' => 'Is Delete',
            'qrcode_pic' => '海报图',
            'pic_list' => '多图模式  图片列表',
            'detail' => '新版专题详情',
            'abstract' => '摘要',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $form = new CommonTopicForm();
        if ($this->search) {
            $search = json_decode($this->search, true);
            $form->keyword = $search['keyword'];
            $form->type = isset($search['type']) ? $search['type'] : '';
        }
        $form->page_size = $this->page_size;
        $form->page = $this->page;
        $res = $form->getList();

        $newList = [];
        /** @var Topic $item */
        foreach ($res['list'] as $item) {
            $arr = ArrayHelper::toArray($item);
            $arr['topicType'] = ArrayHelper::toArray($item->topicType);
            unset($arr['content']);
            $newList[] = $arr;
        }

        $select = TopicType::find()->select('id,name')->where([
            'mall_id' => Yii::$app->mall->id,
            'is_delete' => 0
        ])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'select' => $select,
                'pagination' => $res['pagination']
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Topic::findOne([
            'id' => $this->id,
            'mall_id' => Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = Topic::findOne([
            'id' => $this->id,
            'mall_id' => Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->sort = $this->sort;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功'
        ];
    }
    public function editChosen()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Topic::findOne([
            'id' => $this->id,
            'mall_id' => Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_chosen = $this->is_chosen;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功'
        ];
    }

    //DELETE
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $topic = Topic::find()->where([
            'mall_id' => Yii::$app->mall->id,
            'id' => $this->id
        ])->one();
        if ($topic) {
            $topic->pic_list = $topic->pic_list ? Yii::$app->serializer->decode($topic->pic_list) : [];
            if ($topic->detail) {
                $topic->detail = $topic->detail ? Yii::$app->serializer->decode($topic->detail) : [];
            } else {
                $topic->detail = [
                    [
                        'id' => 'image-text',
                        'data' => [
                            'content' => $topic->content
                        ]
                    ]
                ];
            }
        }
        $select = TopicType::find()->select('id,name')->where([
            'mall_id' => Yii::$app->mall->id,
            'is_delete' => 0
        ])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'topic' => $topic,
                'select' => $select,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Topic::findOne([
            'mall_id' => Yii::$app->mall->id,
            'id' => $this->id
        ]);
        if (!$model) {
            $model = new Topic();
        }

        if (!$this->detail || empty($this->detail)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写专题详情'
            ];
        }
        foreach ($this->detail as $key => $item) {
            if ($item['id'] == 'image-text') {
                if (!$item['data']['content']) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '请填写专题详情'
                    ];
                }
            }
        }
        if ($this->layout == 2) {
            $this->cover_pic = $this->pic_list[0]['url'];
        }
        if (!$this->pic_list) {
            $this->pic_list = [];
        }
        $this->pic_list = Yii::$app->serializer->encode($this->pic_list);
        $this->detail = Yii::$app->serializer->encode($this->detail);
        $this->content = '专题内容';
        $model->attributes = $this->attributes;
        $model->mall_id = Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
