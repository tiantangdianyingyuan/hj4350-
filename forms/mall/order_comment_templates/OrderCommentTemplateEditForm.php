<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_comment_templates;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderCommentsTemplates;

class OrderCommentTemplateEditForm extends Model
{
    public $id;
    public $type;
    public $title;
    public $content;

    public function rules()
    {
        return [
            [['title', 'content', 'type'], 'required'],
            [['id', 'type'], 'integer'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '模板类型',
            'title' => '模板标题',
            'content' => '模板内容',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = OrderCommentsTemplates::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
            'is_delete' => 0,
        ]);

        if (!$model) {
            $model = new OrderCommentsTemplates();
            $model->mall_id = \Yii::$app->mall->id;
            $model->mch_id = \Yii::$app->user->identity->mch_id;
        }
        $model->attributes = $this->attributes;
        $res = $model->save();
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
