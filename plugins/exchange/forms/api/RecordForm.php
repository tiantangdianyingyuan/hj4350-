<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\exchange\ExchangeFactory;

class RecordForm extends Model
{
    public $code;
    public $user_id;
    public $token;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['user_id'], 'number'],
            [['code', 'token'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => '兑换码',
            'user_id' => '小程序用户',
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
                \Yii::$app->user->id,
                $this->token,
                \Yii::$app->appPlatform
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



    public function convert()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $create = new ExchangeFactory(
                $this->code,
                \Yii::$app->user->id,
                $this->token,
                \Yii::$app->appPlatform
            );
            $result = $create->convert();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '领取成功',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
