<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\exchange\ExchangeFactory;
use app\plugins\exchange\models\ExchangeCode;

class RecordForm extends Model
{
    public $code;
    public $user_id;
    public $token;

    public $name;
    public $mobile;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['user_id'], 'integer'],
            [['code', 'token', 'name', 'mobile'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => '兑换码',
        ];
    }

    public function showInfo()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $create = new ExchangeFactory(
            $this->code,
            \Yii::$app->user->id,
            '',
            \Yii::$app->appPlatform
        );
        return $create->showInfo();
    }

    public function unite()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $create = new ExchangeFactory(
                $this->code,
                $this->user_id,
                $this->token,
                ExchangeCode::ORIGIN_ADMIN,
                [
                    'name' => $this->name,
                    'mobile' => $this->mobile,
                ]
            );
            $result = $create->unite();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '已兑换成功',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    //未开发测试
    public function convert()
    {

    }
}
