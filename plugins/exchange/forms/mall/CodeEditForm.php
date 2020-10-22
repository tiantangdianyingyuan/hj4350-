<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\common\CreateCode;
use app\plugins\exchange\models\ExchangeCode;

class CodeEditForm extends Model
{
    public $library_id;
    public $num;
    public $id;
    public function rules()
    {
        return [
            [['library_id', 'num'], 'required'],
            [['library_id', 'num', 'id'], 'integer'],
            [['num'], 'integer', 'max' => 1000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'library_id' => '库',
            'num' => '生成数量',
            'id' => '兑换码',
        ];
    }


    public function ban()
    {
        try {
            if (empty($this->id)) {
                throw new \Exception('请求错误');
            }
            $model = ExchangeCode::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'status' => 1,
            ]);
            if (!$model) {
                throw new \Exception('数据不存在');
            }
            $model->status = 0;
            $model->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '禁用成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function append()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $libraryModel = CommonModel::getLibrary($this->library_id);
            if (!$libraryModel) {
                throw new \Exception('兑换库不合法');
            }

            $create = new CreateCode($libraryModel, \Yii::$app->mall->id);
            $line = $create->createAll($this->num);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => sprintf('成功添加%s条', $line)
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
