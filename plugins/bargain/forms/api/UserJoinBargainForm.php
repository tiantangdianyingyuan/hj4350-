<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 11:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\jobs\UserJoinBargainJob;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\BargainUserOrder;
use app\plugins\bargain\models\Code;
use yii\db\Exception;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserJoinBargainForm extends ApiModel
{

    public $bargain_order_id;

    public function rules()
    {
        return [
            [['bargain_order_id'], 'required']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);
            /* @var BargainUserOrder $userBargainOrder */
            $userBargainOrder = $commonBargainOrder->getUserJoinOrder($this->user->id, $this->bargain_order_id);
            if ($userBargainOrder) {
                throw new \Exception('用户已参与本次砍价');
            }

            /* @var BargainOrder $bargainOrder */
            $bargainOrder = $commonBargainOrder->getBargainOrder($this->bargain_order_id);
            if (!$bargainOrder) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->bargainGoods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->status == 0) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->goodsWarehouse->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->status != Code::BARGAIN_PROGRESS) {
                throw new \Exception('砍价已完成');
            }

            if ($bargainOrder->resetTime <= 0) {
                throw new \Exception('砍价已结束');
            }

            $token = \Yii::$app->security->generateRandomString();
            $queueId = \Yii::$app->queue->delay(0)->push(new UserJoinBargainJob([
                'bargainOrder' => $bargainOrder,
                'mall' => $this->mall,
                'user' => $this->user,
                'token' => $token
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'queueId' => $queueId,
                    'token' => $token
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
