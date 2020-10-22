<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\models\GoodsAttrTemplate;
use app\models\Model;

class GoodsAttrTemplateForm extends Model
{
    public $page;
    public $id;
    public $attr_group_name;
    public $attr_group_id;
    public $attr_list;

    public $page_size;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page', 'attr_group_id', 'page_size'], 'integer'],
            [['attr_group_name', 'keyword'], 'string'],
            [['attr_list'], 'trim'],
            [['attr_group_id'], 'default', 'value' => 0],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'attr_group_name' => '规格名',
            'attr_group_id' => '规格组',
            'attr_list' => '规格值',
        ];
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = GoodsAttrTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if ($this->keyword) {
            $regexp = $this->keyword;
            $concat = sprintf('.*%s.*[[:space:]]', $regexp);

            $query->andWhere([
                'OR',
                ['like', 'attr_group_name', $this->keyword],
                ['REGEXP', 'select_attr_list', $concat]
            ]);
        }

        $template = $query->page($pagination, $this->page_size)->all();
        $template = array_map(function ($item) {
            $item['attr_list'] = json_decode($item['attr_list'], true);
            return $item;
        }, $template);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $template,
                'pagination' => $pagination
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = GoodsAttrTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ])->one();
        if (empty($model)) {
            $model = new GoodsAttrTemplate();
            $model->mch_id = \Yii::$app->user->identity->mch_id;
            $model->mall_id = \Yii::$app->mall->id;
        }

        $model->attributes = $this->attributes;
        $model->attr_list = \yii\helpers\BaseJson::encode($this->attr_list);
        $arr = array_column($this->attr_list, 'attr_name');
        $model->select_attr_list = join("\r", $arr) . "\r";
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = GoodsAttrTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->one();
        if (empty($model)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据已清空'
            ];
        }
        $model->is_delete = 1;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}