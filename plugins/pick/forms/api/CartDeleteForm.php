<?php


/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pick\models\PickCart;

class CartDeleteForm extends Model
{
    public $cart_id_list;

    public function rules()
    {
        return [
            [['cart_id_list'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            PickCart::cacheStatusSet(true);
            $this->cart_id_list = json_decode($this->cart_id_list, true);
            PickCart::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
                'id' => $this->cart_id_list,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
            ]);
            PickCart::cacheStatusSet(false);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
            ];
        } catch (\Exception  $e) {
            PickCart::cacheStatusSet(false);
        }
    }
}
