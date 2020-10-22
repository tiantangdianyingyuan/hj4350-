<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 14:30
 */

namespace app\plugins\vip_card\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\vip_card\jobs\OrderSubmitJob;
use app\plugins\vip_card\models\VipCard;

class OrderSubmitForm extends Model
{
    public $id;
    public $order_form;

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['order_form'], 'string'],
            [['order_form'], 'default', 'value' => '[]'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '会员卡',
        ];
    }

    public function preview()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $data = $this->getVipCardInfo();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data
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

    private function getVipCardInfo()
    {
        $data = VipCard::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with('detail.vipCards')
            ->with('detail.vipCoupons')
            ->asArray()
            ->one();
        if (!$data) {
            throw new \Exception('该会员卡不存在');
        }

        return $data;
    }

    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $token = \Yii::$app->security->generateRandomString();
        $dataArr = [
            'mall' => \Yii::$app->mall,
            'user' => \Yii::$app->user->identity,
            'id' => $this->id,
            'order_form' => json_decode($this->order_form),
            'token' => $token,
        ];
        $class = new OrderSubmitJob($dataArr);
        $queueId = \Yii::$app->queue->delay(0)->push($class);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'token' => $token,
                'queue_id' => $queueId
            ]
        ];
    }
}
