<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\video;

use app\core\response\ApiCode;
use app\models\Video;
use app\models\Model;

class VideoForm extends Model
{
    public $model;
    public $page;
    public $page_size;

    public $id;
    public $title;
    public $type;
    public $url;
    public $pic_url;
    public $content;
    public $sort;

    public function rules()
    {
        return [
            // [['title', 'type', 'content', 'pic_url'], 'required'],
            [['type', 'sort', 'id', 'page'], 'integer'],
            [['content'], 'string'],
            [['title', 'pic_url'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 2048],
            [['sort'], 'default', 'value' => 0],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'type' => '视频来源 0--源地址 1--腾讯视频',
            'url' => '链接',
            'content' => '详情介绍',
            'pic_url' => '缩略图',
            'sort' => '排序',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $list = Video::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])
        ->page($pagination, $this->page_size)
        ->orderBy('sort ASC,id DESC')
        ->asArray()
        ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        
        $model = Video::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
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


    //DELETE
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = Video::find()->where(['mall_id' => \Yii::$app->mall->id,'id' => $this->id])->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Video::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ]);
        if (!$model) {
            $model = new Video();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
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
