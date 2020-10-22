<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/22
 * Time: 9:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\fxhb\forms\common\CommonFxhbDb;

/**
 * @property User $user
 */
class JoinResultForm extends ApiModel
{
    public $user;

    public $queueId;
    public $token;

    public function rules()
    {
        return [
            [['queueId', 'token'], 'required'],
            [['queueId'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!\Yii::$app->queue->isDone($this->queueId)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'retry' => 1
                ]
            ];
        }
        try {
            $common = CommonFxhbDb::getCommon($this->mall);
            $userActivity = $common->getUserActivityByToken($this->token);
            if (!$userActivity) {
                $orderSubmitResult = OrderSubmitResult::findOne([
                    'token' => $this->token,
                ]);
                if ($orderSubmitResult) {
                    throw new \Exception($orderSubmitResult->data);
                }
                throw new \Exception('拆红包失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'user_activity_id' => $userActivity->parent_id == 0 ? $userActivity->id : $userActivity->parent_id
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
