<?php

namespace app\forms\common\notice;

use app\core\response\ApiCode;
use app\models\AdminNotice;
use app\models\Model;

class NoticeCreateForm extends Model
{
    public $id;
    public $type;
    public $content;

    public function rules()
    {
        return [
            [['type', 'content'], 'required'],
            [['type', 'content'], 'string'],
            [['id'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
//           update更新urgent紧急important重要
            $type = ['update', 'urgent', 'important'];
            if (!in_array($this->type, $type)) {
                throw new \Exception('公告类型错误');
            }
            if ($this->id) {
                $model = AdminNotice::findOne($this->id);
            } else {
                $model = new AdminNotice();
            }
            $model->user_id = \Yii::$app->user->id;
            $model->type = $this->type;
            $model->content = $this->content;
            if (!$model->save()) {
                throw new \Exception((new Model())->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '公告发布成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function del()
    {
        try {
            if (!$this->id) {
                throw new \Exception('ID不能为空');
            }
            if (AdminNotice::updateAll(['is_delete' => 1], ['id' => $this->id]) <= 0) {
                throw new \Exception('删除失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
