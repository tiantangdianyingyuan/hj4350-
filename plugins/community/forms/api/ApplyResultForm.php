<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 10:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\forms\api\order\OrderPayFormBase;
use app\models\OrderSubmitResult;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\models\CommunityMiddleman;
use yii\helpers\ArrayHelper;

class ApplyResultForm extends OrderPayFormBase
{
    public $token;
    public $queue_id;
    public $id;

    public function rules()
    {
        return [
            ['token', 'string'],
            [['queue_id', 'id'], 'integer']
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!\Yii::$app->queue->isDone($this->queue_id)) {
            return [
                'code' => 0,
                'data' => [
                    'retry' => 1,
                ],
            ];
        }
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfigByToken($this->token);
        if (!$middleman) {
            $result = OrderSubmitResult::findOne([
                'token' => $this->token
            ]);
            if ($result) {
                return [
                    'code' => 1,
                    'msg' => $result->data,
                ];
            }
            return [
                'code' => 1,
                'msg' => '申请提交失败',
            ];
        }
        if ($middleman->status == -1) {
            return $this->getReturnData($middleman);
        } else {
            return [
                'code' => 0,
                'msg' => '',
                'data' => [
                    'middleman' => $middleman
                ]
            ];
        }
    }

    /**
     * @param CommunityMiddleman $middleman
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     * @return array
     */
    public function getReturnData($middleman)
    {
        $supportPayTypes = [
            Payment::PAY_TYPE_BALANCE,
            Payment::PAY_TYPE_WECHAT,
            Payment::PAY_TYPE_ALIPAY,
            Payment::PAY_TYPE_BAIDU,
            Payment::PAY_TYPE_TOUTIAO,
        ];
        $paymentOrder = new PaymentOrder([
            'title' => '申请成为社区团团长的申请费',
            'amount' => (float)$middleman->pay_price,
            'orderNo' => $middleman->token,
            'notifyClass' => ApplyMoneyNotify::class,
            'supportPayTypes' => $supportPayTypes,
        ]);
        $id = \Yii::$app->payment->createOrder($paymentOrder);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }

    public function applyPay()
    {
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfigById($this->id);
        if (!$middleman) {
            return [
                'code' => 1,
                'msg' => '错误的请求'
            ];
        }
        return $this->getReturnData($middleman);
    }
}
