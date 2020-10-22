<?php

namespace app\forms\mall\cat;


use app\core\response\ApiCode;
use app\forms\common\CommonCats;
use app\models\Model;
use app\models\QuickShopCats;

class QuickShopCatForm extends Model
{
    public $id;
    public $sort;
    public $cat_id;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'sort', 'cat_id'], 'integer'],
            [['sort'], 'default', 'value' => 0],
            [['keyword',], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = QuickShopCats::find()->alias('b')->where([
            'b.is_delete' => 0,
            'b.mall_id' => \Yii::$app->mall->id,
        ])->joinWith(['cats c' => function ($query) {
            $query->where([
                'c.mall_id' => \Yii::$app->mall->id,
                'c.is_delete' => 0
            ]);
        }])->keyword($this->keyword, ['like', 'c.name', $this->keyword]);

        $list = $query->orderBy('sort ASC, id DESC')->page($pagination)->asArray()->all();

        $cats = CommonCats::getAllCats();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'cats' => $cats,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = QuickShopCats::findOne([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'cat_id' => $this->cat_id
        ]);
        if ($model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '分类已存在',
            ];
        }

        $model = new QuickShopCats();
        $model->mall_id = \Yii::$app->mall->id;
        $model->attributes = $this->attributes;
        $model->is_delete = 0;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }

    //editSort
    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = QuickShopCats::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->sort = $this->sort;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = QuickShopCats::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
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
}
