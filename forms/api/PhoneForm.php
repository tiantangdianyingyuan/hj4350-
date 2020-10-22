<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\models\{Model, User};

abstract class PhoneForm extends Model
{
    abstract protected function getPhone();

    public function binding()
    {
        try {
            $data = $this->getPhone();
            $user = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
            if (!$user || !$data) {
                throw new \Exception('获取失败');
            };
            $user->mobile = $data['phoneNumber'];
            $user->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '绑定成功',
                'data' => [
                    'mobile' => $data['phoneNumber'],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }

    }
}
