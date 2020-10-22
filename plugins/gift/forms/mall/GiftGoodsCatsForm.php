<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\mall;


use app\core\response\ApiCode;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\gift\models\GiftGoodsCats;

class GiftGoodsCatsForm extends Model
{
    public $id;
    public $cat_id;
    public $sort;
    public $keyword;
    public $order_by;

    public function rules()
    {
        return [
            [['id', 'cat_id', 'sort'], 'integer'],
            [['keyword', 'order_by'], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftGoodsCats::find()->alias('gc')->leftJoin(['c' => GoodsCats::tableName()], 'c.id = gc.cat_id')
                ->where(['gc.mall_id' => \Yii::$app->mall->id, 'gc.is_delete' => 0, 'c.is_delete' => 0, 'c.status' => 1, 'c.is_show' => 1])->orderBy($this->order_by ?? 'gc.id desc');
            if ($this->keyword) {
                $model->andWhere(['like', 'c.name', $this->keyword]);
            }
            $list = $model->select('gc.id,gc.sort,c.name,c.pic_url')->page($pagination)->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function add()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            if (!$this->cat_id) {
                throw new \Exception('缺少分类ID');
            }
            if (GiftGoodsCats::findOne(['cat_id' => $this->cat_id, 'is_delete' => 0])) {
                throw new \Exception('该分类已存在');
            }
            $model = new GiftGoodsCats();
            $model->mall_id = \Yii::$app->mall->id;
            $model->cat_id = $this->cat_id;
            if (!$model->save()) {
                throw new \Exception((new Model())->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function sort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            if (!$this->id) {
                throw new \Exception('缺少ID');
            }
            if (!$this->sort) {
                throw new \Exception('确实排序字段');
            }
            $model = GiftGoodsCats::findOne($this->id);
            $model->mall_id = \Yii::$app->mall->id;
            $model->sort = $this->sort;
            if (!$model->save()) {
                throw new \Exception((new Model())->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function del()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            if (!$this->id) {
                throw new \Exception('缺少ID');
            }
            if (GiftGoodsCats::updateAll(['is_delete' => 1], ['id' => $this->id]) <= 0) {
                throw new \Exception('删除失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}