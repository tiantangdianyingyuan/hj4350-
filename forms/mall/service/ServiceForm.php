<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\service;


use app\core\response\ApiCode;
use app\models\GoodsServices;
use app\models\Model;

class ServiceForm extends Model
{
    public $id;
    public $page;
    public $is_default;
    public $keyword;
    public $mch_id;


    public function rules()
    {
        return [
            [['id', 'is_default', 'mch_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = GoodsServices::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->page($pagination)
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()->all();

        foreach ($list as &$item) {
            if (empty($item['pic'])) {
                $item['pic'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/statics/img/mall/goods/guarantee/service-pic.png";
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getOptionList()
    {
        $list = $this->getAllServices();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function getAllServices()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = GoodsServices::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_ASC])->all();

        $newList = [];
        /** @var GoodsServices $item */
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->id,
                'name' => $item->name,
                'is_default' => $item->is_default,
            ];
        }

        return $newList;
    }

    public function getDetail()
    {
        $detail = GoodsServices::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ])->asArray()->one();

        if ($detail) {
            if ($detail['pic'] == '') {
                $detail['pic'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
                    "/statics/img/mall/goods/guarantee/service-pic.png";
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '请求失败',
        ];
    }

    public function destroy()
    {
        $services = GoodsServices::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        try {
            $services->is_delete = 1;
            $res = $services->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $this->getErrorMsg($services),
                ];
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

    public function switchChange()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $services = GoodsServices::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        $services->is_default = $this->is_default;
        $res = $services->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($services)
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }
}
