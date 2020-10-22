<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_block;


use app\core\response\ApiCode;
use app\models\HomeBlock;
use app\models\Model;

class HomeBlockForm extends Model
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '魔方ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = HomeBlock::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->asArray()->all();

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
        $detail = HomeBlock::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->asArray()->one();

        if ($detail) {
            if ($detail['value']) {
                $detail['value'] = json_decode($detail['value'], true);
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
        try {
            $homeBlock = HomeBlock::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$homeBlock) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $homeBlock->is_delete = 1;
            $res = $homeBlock->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '删除失败',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
