<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/27
 * Time: 10:23
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\api;


use app\core\response\ApiCode;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\jobs\SignInJob;

class SignInForm extends ApiModel
{
    public $status;
    public $day;

    public function rules()
    {
        return [
            [['status', 'day'], 'required'],
            [['status'], 'in', 'range' => [1, 2, 3]],
            [['day'], 'default', 'value' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '签到类型',
            'day' => '天数'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = Common::getCommon($this->mall);
            $award = $common->getAward($this->status);
            $award->user = $this->user;
            $award->check();
            $token = \Yii::$app->security->generateRandomString();
            $queueId = \Yii::$app->queue->delay(0)->push(new SignInJob([
                'mall' => $this->mall,
                'user' => $this->user,
                'token' => $token,
                'status' => $this->status,
                'day' => $this->day,
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
