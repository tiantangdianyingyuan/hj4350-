<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/24
 * Time: 10:42
 */

namespace app\forms\mall\finance;

use app\core\response\ApiCode;
use app\models\Model;

class CashApplyForm extends Model
{
    public $mall;

    public $id;
    public $status;
    public $content;
    public $model;

    public function rules()
    {
        return [
            [['id', 'status', 'model'], 'required'],
            [['id', 'status'], 'integer'],
            ['status', 'in', 'range' => [1, 2, 3]],
            ['content', 'trim'],
            ['content', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => '备注'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $factory = new FinanceFactory();
            $class = $factory->create($this->model);
            $class->attributes = $this->attributes;
            return $class->save();
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

    public function remark()
    {
        try {
            $factory = new FinanceFactory();
            $class = $factory->create($this->model);
            $class->attributes = $this->attributes;
            return $class->remark();
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
