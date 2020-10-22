<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 13:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\jobs\JoinActivityJob;

/**
 * @property User $user
 */
class JoinForm extends ApiModel
{
    public $user;

    public $user_activity_id;

    public function rules()
    {
        return [
            [['user_activity_id'], 'integer'],
            [['user_activity_id'], 'default', 'value' => 0],
        ];
    }

    public function join()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $parentActivity = CommonFxhbDb::getCommon($this->mall)->getUserActivityById($this->user_activity_id, null);
        if ($parentActivity) {
            if ($parentActivity->user_id == $this->user->id) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '本人不能拆自己的红包'
                ];
            }
            if ($parentActivity->activity->status == 0) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '活动已结束'
                ];
            }
            $token = $parentActivity->token;
        } else {
            $token = \Yii::$app->security->generateRandomString();
        }

        $queueId = \Yii::$app->queue->delay(0)->push(new JoinActivityJob([
            'mall' => $this->mall,
            'user' => $this->user,
            'user_activity_id' => $this->user_activity_id,
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
    }
}
