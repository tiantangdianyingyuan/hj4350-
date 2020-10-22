<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_nav;


use app\core\response\ApiCode;
use app\models\HomeNav;
use app\models\Model;

class HomeNavForm extends Model
{
    public $id;
    public $page;
    public $status;
    public $keyword;
    public $limit;

    public function rules()
    {
        return [
            [['id', 'status', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['limit'], 'default', 'value' => 20],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = HomeNav::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->keyword($this->keyword, ['like', 'name', $this->keyword]);

        $list = $query->page($pagination, $this->limit)
            ->orderBy('sort ASC')
            ->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        /* @var HomeNav $detail */
        $detail = HomeNav::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->one();

        $detail->params = $detail->params ? \Yii::$app->serializer->decode($detail->params) : [];
        if ($detail) {
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
        try {
            $homeNav = HomeNav::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$homeNav) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $homeNav->is_delete = 1;
            $res = $homeNav->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $this->getErrorMsg($homeNav),
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
            ];
        }
    }

    public function status()
    {
        try {
            $homeNav = HomeNav::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$homeNav) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $homeNav->status = $this->status;
            $res = $homeNav->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '更新失败',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
