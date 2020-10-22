<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 15:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainUserOrder;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserJoinBargainResultForm extends ApiModel
{

    public $queueId;
    public $token;

    public function rules()
    {
        return [
            [['queueId', 'token'], 'required']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->queueId != 'undefined' && !\Yii::$app->queue->isDone($this->queueId)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'retry' => 1
                ]
            ];
        }

        $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);
        /* @var OrderSubmitResult $result */
        $result = $commonBargainOrder->getBargainUserOrderResult($this->token);
        if ($result) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $result->data
            ];
        }

        /* @var BargainUserOrder $bargainUserOrder */
        $bargainUserOrder = $commonBargainOrder->getBargainUserOrderByToken($this->token);
        $bargainOrder = $commonBargainOrder->getBargainOrder($bargainUserOrder->bargain_order_id);
        $info = [
            'user_id' => $bargainUserOrder->user_id,
            'nickname' => $bargainUserOrder->user->nickname,
            'price' => $bargainUserOrder->price
        ];

        return [
            'code' => 0,
            'data' => [
                'info' => $info,
                'bargain' => [
                    'user_id' => $bargainOrder->user_id,
                    'nickname' => $bargainOrder->user->nickname,
                    'avatar' => $bargainOrder->user->userInfo->avatar
                ]
            ]
        ];
    }
}
