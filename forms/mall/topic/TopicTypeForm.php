<?php
/**
* link: http://www.zjhejiang.com/
* copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
* author: xay
*/
namespace app\forms\mall\topic;

use app\core\response\ApiCode;
use app\models\TopicType;
use app\models\Model;

class TopicTypeForm extends Model
{
    public $model;
    public $page;
    public $page_size;

    public $id;
    public $mall_id;
    public $name;
    public $sort;
    public $status;
    public $is_delete;
    public $keyword;

    public function rules()
    {
        return [
            [['sort', 'is_delete','id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['is_delete', 'sort','status'], 'default', 'value' => 0],
            [['sort'], 'integer', 'min' => 0, 'max' => 999999999],
            [['page'], 'default', 'value' => 1],
            [['page_size'], 'default', 'value' => 10],
            [['keyword'], 'string'],
            [['keyword'], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '名称',
            'status' => '状态',
            'sort' => '排序',
            'is_delete' => 'Is Delete',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = TopicType::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->keyword($this->keyword, ['like', 'name', $this->keyword])
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

    public function switchStatus()
    {
        $model = TopicType::findOne([
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
        $model->status = $this->status;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = TopicType::findOne([
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

    //DETAIL
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $topicType = TopicType::findOne(['mall_id' => \Yii::$app->mall->id,'id' => $this->id]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => (object)$topicType
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $model = TopicType::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new TopicType();
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
